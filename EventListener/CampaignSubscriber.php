<?php
namespace MauticPlugin\CustomEmailBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\EmailBundle\Helper\MailHelper;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\CustomEmailBundle\Form\Type\CustomEmailType;
use MauticPlugin\CustomEmailBundle\Entity\CustomEmailLog;
use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\PendingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CampaignSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $em;
    private MailHelper $mailHelper;
    private LeadModel $leadModel;

    public function __construct(
        EntityManagerInterface $em,
        MailHelper $mailHelper,
        LeadModel $leadModel
    ) {
        $this->em = $em;
        $this->mailHelper = $mailHelper;
        $this->leadModel = $leadModel;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD => ['onCampaignBuild', 0],
            'plugin.custom.email.send'       => ['onExecute', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $event->addAction('custom.email.send', [
            'label'          => 'Send Custom Email',
            'description'    => 'Send an email with custom rules (basic)',
            'batchEventName' => 'plugin.custom.email.send',
            'formType'       => CustomEmailType::class,
        ]);
    }

    public function onExecute(PendingEvent $event): void
    {
        $config   = $event->getConfig();
        $contacts = $event->getContactsKeyedById();

        $subject = $config->getProperty('subject') ?? 'No Subject';
        $body    = $config->getProperty('body') ?? 'No Body';

        foreach ($contacts as $contact) {
            $email = $contact->getEmail();
            $log   = $event->findLogByContactId($contact->getId());

            if (!$email) {
                $event->fail($log, 'Missing email');
                continue;
            }

            try {
                // Use Mautic MailHelper properly
                $this->mailHelper->message->setTo($email);
                $this->mailHelper->message->setSubject($subject);
                $this->mailHelper->message->setHtmlBody($body);
                $this->mailHelper->sendMessage();

                $logEntry = new CustomEmailLog();
                $logEntry->setLead($contact);
                $logEntry->setSentAt(new \DateTime());
                $logEntry->setStatus('sent');
                $this->em->persist($logEntry);
                $this->em->flush();

                $event->pass($log);
            } catch (\Exception $e) {
                $event->fail($log, $e->getMessage());
            }
        }

        $event->passRemaining();
    }
}
