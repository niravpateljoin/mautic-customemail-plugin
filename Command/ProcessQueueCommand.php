<?php
namespace MauticPlugin\CustomEmailBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\EmailBundle\Helper\MailHelper;
use MauticPlugin\CustomEmailBundle\Entity\CustomEmailQueue;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\EmailBundle\Entity\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessQueueCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('customemail:process-queue')
            ->setDescription('Process pending custom email queue with dates, limits, and throttling.');
    }

    public function __construct(
        private EntityManagerInterface $em,
        private MailHelper $mailHelper,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();

        // Get all pending items
        $queueItems = $this->em->getRepository(CustomEmailQueue::class)
            ->findBy(['status' => 'pending'], ['scheduledAt' => 'ASC', 'id' => 'ASC']);

        if (!$queueItems) {
            $output->writeln('<info>No pending emails in the queue.</info>');
            return Command::SUCCESS;
        }

        // Prepare counters
        $todayStart = new \DateTimeImmutable('today');
        $todayEnd   = (new \DateTimeImmutable('today'))->modify('+1 day');

        $sentTodayByGroup = [];
        $limitTodayByGroup = [];
        
        foreach ($queueItems as $item) {
            $groupKey = $item->getCampaignId().':'.((string)($item->getEmailId() ?? 0));

            if (!array_key_exists($groupKey, $sentTodayByGroup)) {
                // Calculate today's limit
                $baseLimit = $item->getDailyLimit();
                $increment = $item->getDailyIncrement() ?? 0;
                $start     = $item->getStartDate();

                if ($baseLimit && $start) {
                    $daysSinceStart = (int)$start->diff($now)->format('%a');
                    $calcLimit = (int)floor(
                        $baseLimit + ($baseLimit * ($increment / 100.0) * $daysSinceStart)
                    );
                    $limitTodayByGroup[$groupKey] = max(1, $calcLimit);
                } else {
                    $limitTodayByGroup[$groupKey] = $baseLimit ?: null; // unlimited
                }

                // Count already sent today
                $q = $this->em->getRepository(CustomEmailQueue::class)->createQueryBuilder('q')
                    ->select('COUNT(q.id)')
                    ->where('q.campaignId = :cid')
                    ->andWhere('q.status = :sent')
                    ->andWhere('q.sentAt >= :start')
                    ->andWhere('q.sentAt < :end')
                    ->setParameters([
                        'cid'   => $item->getCampaignId(),
                        'sent'  => 'sent',
                        'start' => $todayStart,
                        'end'   => $todayEnd,
                    ]);

                $alreadySent = (int)$q->getQuery()->getSingleScalarResult();
                $sentTodayByGroup[$groupKey] = $alreadySent;
            }
        }

        foreach ($queueItems as $item) {
            // Respect start & end dates
            if ($item->getStartDate() && $now < $item->getStartDate()) {
                continue;
            }
            if ($item->getEndDate() && $now > $item->getEndDate()) {
                $item->setStatus('failed');
                $this->em->persist($item);
                continue;
            }

            // Respect scheduledAt
            if ($item->getScheduledAt() && $now < $item->getScheduledAt()) {
                continue;
            }

            // Limit check
            $groupKey   = $item->getCampaignId().':'.((string)($item->getEmailId() ?? 0));
            $limitToday = $limitTodayByGroup[$groupKey] ?? null;
            $sentToday  = $sentTodayByGroup[$groupKey] ?? 0;

            if ($limitToday !== null && $sentToday >= $limitToday) {
                continue;
            }

            // Load lead & email
            $lead  = $this->em->getRepository(Lead::class)->find($item->getContactId());
            $email = $item->getEmailId()
                ? $this->em->getRepository(Email::class)->find($item->getEmailId())
                : null;

            if (!$lead) {
                $item->setStatus('failed');
                $this->em->persist($item);
                $output->writeln("<error>Lead {$item->getContactId()} not found.</error>");
                continue;
            }

            try {
                if ($email) {
                    $this->mailHelper->setEmail($email);
                    $this->mailHelper->send($lead, $lead->getProfileFields() ?? []);
                } else {
                    $this->mailHelper->message->setSubject($item->getSubject());
                    $this->mailHelper->message->setHtmlBody($item->getBody());
                    $this->mailHelper->message->setTo($lead->getEmail());
                    $this->mailHelper->sendMessage();
                }

                $item->setStatus('sent');
                $item->setSentAt(new \DateTime());
                $this->em->persist($item);

                $sentTodayByGroup[$groupKey] = ($sentTodayByGroup[$groupKey] ?? 0) + 1;

                $output->writeln("<info>Sent to lead ID {$lead->getId()} ({$lead->getEmail()})</info>");
            } catch (\Throwable $e) {
                $item->setStatus('failed');
                $this->em->persist($item);
                $msg = sprintf('Failed to send to lead %d: %s', $lead->getId(), $e->getMessage());
                $this->logger->error('[CustomEmail] '.$msg, ['exception' => $e]);
                $output->writeln("<error>$msg</error>");
                continue;
            }

            // Throttle
            $unit  = $item->getSendingSpeedUnit() ?: 'seconds';
            $value = (int)($item->getSendingSpeedValue() ?: 0);
            $delay = $unit === 'minutes' ? $value * 60 : $value;

            if ($delay > 0) {
                sleep($delay);
            }
        }

        $this->em->flush();
        $output->writeln('<info>Queue processing finished.</info>');

        return Command::SUCCESS;
    }
}