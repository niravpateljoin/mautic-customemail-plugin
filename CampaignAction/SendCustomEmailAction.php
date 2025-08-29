<?php

namespace MauticPlugin\CustomEmailBundle\CampaignAction;

use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mautic\EmailBundle\Model\EmailModel;
use MauticPlugin\CustomEmailBundle\Entity\CustomEmailQueue;
use Doctrine\ORM\EntityManagerInterface;

class SendCustomEmailAction implements EventSubscriberInterface
{
    private $emailModel;
    private $em;

    public function __construct(EmailModel $emailModel, EntityManagerInterface $em)
    {
        $this->emailModel = $emailModel;
        $this->em = $em;
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
        $contact = $event->getLead();

        $queue = new CustomEmailQueue();
        $queue->setCampaignId($event->getCampaign()->getId());
        $queue->setContactId($contact->getId());
        $queue->setEmailId($config['email'] ?? null);
        $queue->setSubject($config['subject'] ?? '');
        $queue->setBody($config['body'] ?? '');
        $queue->setStartDate(!empty($config['startDate']) ? new \DateTime($config['startDate']) : null);
        $queue->setEndDate(!empty($config['endDate']) ? new \DateTime($config['endDate']) : null);
        $queue->setSendingSpeedUnit($config['sending_speed_unit'] ?? null);
        $queue->setSendingSpeedValue($config['sending_speed_value'] ?? null);
        $queue->setDailyLimit($config['daily_limit'] ?? null);
        $queue->setDailyIncrement($config['daily_increment'] ?? null);

        $this->em->persist($queue);
        $this->em->flush();
    }
}
