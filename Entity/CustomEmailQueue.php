<?php

declare(strict_types=1);

namespace MauticPlugin\CustomEmailBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

class CustomEmailQueue
{
    protected int $id;
    protected int $campaignId;
    protected int $contactId;
    protected ?int $emailId = null;
    protected string $subject;
    protected string $body;
    protected string $status = 'pending';
    protected ?\DateTimeInterface $scheduledAt = null;
    protected ?\DateTimeInterface $sentAt = null;
    protected \DateTimeInterface $createdAt;
    protected ?\DateTimeInterface $startDate = null;
    protected ?\DateTimeInterface $endDate = null;
    protected ?string $sendingSpeedUnit = null;
    protected ?int $sendingSpeedValue = null;
    protected ?int $dailyLimit = null;
    protected ?int $dailyIncrement = null;

    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('custom_email_queue');

        $builder->addId();

        $builder->addNamedField('campaignId', Types::INTEGER, 'campaign_id');
        $builder->addNamedField('contactId', Types::INTEGER, 'contact_id');
        $builder->addNamedField('emailId', Types::INTEGER, 'email_id', true);
        $builder->addNamedField('subject', Types::STRING, 'subject', false, ['length' => 255]);
        $builder->addNamedField('body', Types::TEXT, 'body');
        $builder->addNamedField('status', Types::STRING, 'status', false, ['length' => 20, 'default' => 'pending']);
        $builder->addNamedField('scheduledAt', Types::DATETIME_MUTABLE, 'scheduled_at', true);
        $builder->addNamedField('sentAt', Types::DATETIME_MUTABLE, 'sent_at', true);
        $builder->addNamedField('createdAt', Types::DATETIME_MUTABLE, 'created_at');
        $builder->addNamedField('startDate', Types::DATETIME_MUTABLE, 'start_date', true);
        $builder->addNamedField('endDate', Types::DATETIME_MUTABLE, 'end_date', true);
        $builder->addNamedField('sendingSpeedUnit', Types::STRING, 'sending_speed_unit', true, ['length' => 10]);
        $builder->addNamedField('sendingSpeedValue', Types::INTEGER, 'sending_speed_value', true);
        $builder->addNamedField('dailyLimit', Types::INTEGER, 'daily_limit', true);
        $builder->addNamedField('dailyIncrement', Types::INTEGER, 'daily_increment', true);

        $builder->addIndex(['campaign_id', 'contact_id'], 'idx_custom_queue_campaign_contact');
        $builder->addIndex(['status', 'scheduled_at'], 'idx_custom_queue_status_scheduled');
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }


    public function getId(): int { return $this->id; }

    public function getCampaignId(): int { return $this->campaignId; }
    public function setCampaignId(int $campaignId): self { $this->campaignId = $campaignId; return $this; }

    public function getContactId(): int { return $this->contactId; }
    public function setContactId(int $contactId): self { $this->contactId = $contactId; return $this; }

    public function getEmailId(): ?int { return $this->emailId; }
    public function setEmailId(?int $emailId): self { $this->emailId = $emailId; return $this; }

    public function getSubject(): string { return $this->subject; }
    public function setSubject(string $subject): self { $this->subject = $subject; return $this; }

    public function getBody(): string { return $this->body; }
    public function setBody(string $body): self { $this->body = $body; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getScheduledAt(): ?\DateTimeInterface { return $this->scheduledAt; }
    public function setScheduledAt(?\DateTimeInterface $scheduledAt): self { $this->scheduledAt = $scheduledAt; return $this; }

    public function getSentAt(): ?\DateTimeInterface { return $this->sentAt; }
    public function setSentAt(?\DateTimeInterface $sentAt): self { $this->sentAt = $sentAt; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getStartDate(): ?\DateTimeInterface { return $this->startDate; }
    public function setStartDate(?\DateTimeInterface $startDate): self { $this->startDate = $startDate; return $this; }

    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
    public function setEndDate(?\DateTimeInterface $endDate): self { $this->endDate = $endDate; return $this; }

    public function getSendingSpeedUnit(): ?string { return $this->sendingSpeedUnit; }
    public function setSendingSpeedUnit(?string $sendingSpeedUnit): self { $this->sendingSpeedUnit = $sendingSpeedUnit; return $this; }

    public function getSendingSpeedValue(): ?int { return $this->sendingSpeedValue; }
    public function setSendingSpeedValue(?int $sendingSpeedValue): self { $this->sendingSpeedValue = $sendingSpeedValue; return $this; }

    public function getDailyLimit(): ?int { return $this->dailyLimit; }
    public function setDailyLimit(?int $dailyLimit): self { $this->dailyLimit = $dailyLimit; return $this; }

    public function getDailyIncrement(): ?int { return $this->dailyIncrement; }
    public function setDailyIncrement(?int $dailyIncrement): self { $this->dailyIncrement = $dailyIncrement; return $this; }
}
