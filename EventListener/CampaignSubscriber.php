<?php

namespace MauticPlugin\CustomEmailBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\PendingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MauticPlugin\CustomEmailBundle\Form\Type\CustomEmailType;

final class CampaignSubscriber implements EventSubscriberInterface
{
    public const TYPE = 'custom.email.send';
    public const EVENT_EXECUTE = 'plugin.custom.email.send';

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD => ['onCampaignBuild', 0],
            self::EVENT_EXECUTE               => ['onExecute', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $event->addAction(self::TYPE, [
            'label'          => 'Send Custom Email',
            'description'    => 'Send an email with custom rules (basic)',
            'batchEventName' => self::EVENT_EXECUTE,   // << use batch event for actions
            'formType'       => CustomEmailType::class,
        ]);
    }

    public function onExecute(PendingEvent $event): void
    {
        // $config   = $event->getConfig();             // accessor object
        // $contacts = $event->getContactsKeyedById();  // batch of contacts

        // foreach ($contacts as $contact) {
        //     $log   = $event->findLogByContactId($contact->getId());
        //     $email = $contact->getEmail();

        //     if (!$email) {
        //         // mark this one failed (retry depends on your settings)
        //         $event->fail($log, 'Missing email');
        //         continue;
        //     }

        //     // TODO: send real email; for now just log
        //     error_log("Custom Email to {$email}");
        //     error_log('Subject: ' . ($config->getProperty('subject') ?? ''));
        //     error_log('Body: ' . ($config->getProperty('body') ?? ''));

        //     // mark this contact passed
        //     $event->pass($log);
        // }

        // // If any remain unhandled:
        // $event->passRemaining();
            $config   = $event->getConfig();
            $contacts = $event->getContactsKeyedById();

            $now       = new \DateTime();
            $startDate = $config->getProperty('start_date');
            $endDate   = $config->getProperty('end_date');

            // Date filtering
            if ($startDate && $now < new \DateTime($startDate)) {
                $event->failRemaining('Start date not reached yet');
                return;
            }
            if ($endDate && $now > new \DateTime($endDate)) {
                $event->failRemaining('End date passed');
                return;
            }

            // Daily limit logic
            $dailyLimit    = (int) ($config->getProperty('daily_limit') ?? 0);
            $dailyIncrement= (int) ($config->getProperty('daily_increment') ?? 0);

            $today = $now->format('Y-m-d');
            $sentToday = $this->getSentCountToday($today); // ← You need to implement DB or file storage for counts

            // Adjust daily limit with increment
            if ($dailyLimit > 0) {
                $daysSinceStart = $startDate ? (new \DateTime($startDate))->diff($now)->days : 0;
                $dailyLimit += floor(($dailyLimit * $dailyIncrement / 100) * $daysSinceStart);
            }

            $sent = 0;
            foreach ($contacts as $contact) {
                if ($dailyLimit && $sentToday + $sent >= $dailyLimit) {
                    $event->failRemaining('Daily sending limit reached');
                    return;
                }

                $email = $contact->getEmail();
                if (!$email) {
                    $log = $event->findLogByContactId($contact->getId());
                    $event->fail($log, 'Missing email');
                    continue;
                }

                // Send email (replace this with real sending logic later)
                error_log("Custom Email to {$email}");
                error_log('Subject: ' . ($config->getProperty('subject') ?? ''));
                error_log('Body: ' . ($config->getProperty('body') ?? ''));

                $log = $event->findLogByContactId($contact->getId());
                $event->pass($log);
                $sent++;

                // Sending speed (delay)
                $unit  = $config->getProperty('sending_speed_unit');
                $value = (int) $config->getProperty('sending_speed_value');
                if ($unit && $value) {
                    $delay = $unit === 'minutes' ? $value * 60 : $value;
                    sleep($delay);
                }
            }

            $event->passRemaining();
    }
}
