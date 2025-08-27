<?php
// namespace MauticPlugin\CustomEmailBundle\Command;

// use Doctrine\DBAL\Connection;
// use Mautic\EmailBundle\Helper\MailHelper;
// use Symfony\Component\Console\Command\Command;
// use Symfony\Component\Console\Input\InputInterface;
// use Symfony\Component\Console\Output\OutputInterface;

// class ProcessQueueCommand extends Command
// {
//     protected static $defaultName = 'customemail:process-queue';

//     private Connection $connection;
//     private MailHelper $mailerHelper;

//     public function __construct(Connection $connection, MailHelper $mailerHelper)
//     {
//         $this->connection   = $connection;
//         $this->mailerHelper = $mailerHelper;
//         parent::__construct();
//     }

//     protected function configure()
//     {
//         $this->setDescription('Process pending custom email queue');
//     }

//     protected function execute(InputInterface $input, OutputInterface $output): int
//     {
//         $output->writeln('Queue command works!');
//         return Command::SUCCESS;
//     }
// }

namespace MauticPlugin\CustomEmailBundle\Command;

use Doctrine\DBAL\Connection;
use Mautic\EmailBundle\Helper\MailHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessQueueCommand extends Command
{
    protected static $defaultName = 'customemail:process-queue';
    protected static $defaultDescription = 'Process pending custom email queue.';

    public function __construct(
        private Connection $connection,
        private MailHelper $mailerHelper
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dailyLimit = 5;    // Fetch from plugin settings if needed
        $delaySeconds = 3;  // Delay between emails

        $output->writeln("<info>Processing Custom Email Queue...</info>");

        $queueItems = $this->connection->fetchAllAssociative(
            "SELECT * FROM custom_email_queue
             WHERE status = 'pending' AND (scheduled_at IS NULL OR scheduled_at <= NOW())
             ORDER BY id ASC
             LIMIT $dailyLimit"
        );

        foreach ($queueItems as $item) {
            try {
                $output->writeln("Sending to contact_id: {$item['contact_id']}");

                $mailer = $this->mailerHelper->getMailer();
                $message = (new \Swift_Message($item['subject']))
                    ->setFrom('you@example.com') // Change this to your sender
                    ->setTo('contact'.$item['contact_id'].'@example.com') // Replace with real contact email
                    ->setBody($item['body']);

                $mailer->send($message);

                // Mark as sent
                $this->connection->update('custom_email_queue', [
                    'status'  => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                ], ['id' => $item['id']]);

                sleep($delaySeconds);
            } catch (\Exception $e) {
                $this->connection->update('custom_email_queue', [
                    'status' => 'failed',
                ], ['id' => $item['id']]);

                $output->writeln("<error>Failed sending to contact_id {$item['contact_id']}: {$e->getMessage()}</error>");
            }
        }

        $output->writeln("<info>Queue Processing Completed</info>");
        return Command::SUCCESS;
    }
}
