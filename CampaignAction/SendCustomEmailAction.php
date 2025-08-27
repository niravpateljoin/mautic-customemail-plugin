<?php

namespace MauticPlugin\CustomEmailBundle\CampaignAction;

use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Entity\Lead;

class SendCustomEmailAction implements EventSubscriberInterface
{
    private $emailModel;

    public function __construct(EmailModel $emailModel)
    {
        $this->emailModel = $emailModel;
    }

    public static function getSubscribedEvents()
    {
        return [
            'plugin.custom.email.send' => ['execute', 0],
        ];
    }

    public function execute(CampaignExecutionEvent $event)
    {
        $config  = $event->getConfig();
        $contact = $event->getContact();

        if (!$contact || !$contact->getEmail()) {
            return;
        }

        // Prepare subject & body from config
        $subject = $config['subject'] ?? 'Default Subject';
        $body    = $config['body'] ?? 'Default Body';

        // Fake minimal email array (Mautic EmailModel expects this format)
        $email = [
            'subject' => $subject,
            'customHtml' => $body,
            'fromName' => 'Custom Plugin',
            'fromAddress' => 'no-reply@example.com',
        ];

        // Actually send email
        $this->emailModel->sendEmail($email, $contact);

        // Log for debugging
        error_log("✅ Custom Email sent to: " . $contact->getEmail());

        $event->setResult(true);
    }
}
