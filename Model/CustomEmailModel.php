<?php

namespace MauticPlugin\CustomEmailBundle\Model;
use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;
use MauticPlugin\CustomEmailBundle\Entity\CustomEmailQueue;

class CustomEmailModel
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    /**
     * Add a new email to the custom queue
     *
     * @param int   $campaignId
     * @param int   $contactId
     * @param array $config  (all optional fields)
     */
    public function addToQueue(int $campaignId, int $contactId, array $config = []): CustomEmailQueue
    {
        $queue = new CustomEmailQueue();

        // Required fields
        $queue->setCampaignId($campaignId);
        $queue->setContactId($contactId);

        // Optional fields with defaults
        $queue->setEmailId($config['email_id'] ?? null);
        $queue->setSubject($config['subject'] ?? ''); // can be empty
        $queue->setBody($config['body'] ?? '');
        $queue->setStatus($config['status'] ?? 'pending');

        // Scheduling
        $queue->setScheduledAt(
            !empty($config['scheduled_at'])
                ? new \DateTime($config['scheduled_at'])
                : null
        );
        $queue->setSentAt(
            !empty($config['sent_at'])
                ? new \DateTime($config['sent_at'])
                : null
        );

        // Start/End window
        $queue->setStartDate(
            !empty($config['start_date'])
                ? new \DateTime($config['start_date'])
                : null
        );
        $queue->setEndDate(
            !empty($config['end_date'])
                ? new \DateTime($config['end_date'])
                : null
        );

        // Sending speed
        $queue->setSendingSpeedUnit($config['sending_speed_unit'] ?? null); // seconds|minutes
        $queue->setSendingSpeedValue($config['sending_speed_value'] ?? null);

        // Limits
        $queue->setDailyLimit($config['daily_limit'] ?? null);
        $queue->setDailyIncrement($config['daily_increment'] ?? null);

        // Created at will be set in constructor
       try {
            $this->em->persist($queue);
            $this->em->flush();
        } catch (\Exception $e) {
            $this->logger->error('Failed to save email to the queue', ['error' => $e->getMessage()]);
        }


        return $queue;
    }
}
