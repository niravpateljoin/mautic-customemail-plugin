<?php

namespace MauticPlugin\CustomEmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="custom_email_queue")
 */
class CustomEmailQueue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $campaign_id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $contact_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $subject;

    /**
     * @ORM\Column(type="text")
     */
    private string $body;

    /**
     * @ORM\Column(type="string", length=20, options={"default":"pending"})
     */
    private string $status = 'pending';

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $scheduled_at = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $sent_at = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $created_at;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    // -----------------------------
    // Getters & Setters
    // -----------------------------
    public function getId(): int
    {
        return $this->id;
    }

    public function getCampaignId(): int
    {
        return $this->campaign_id;
    }

    public function setCampaignId(int $campaign_id): self
    {
        $this->campaign_id = $campaign_id;
        return $this;
    }

    public function getContactId(): int
    {
        return $this->contact_id;
    }

    public function setContactId(int $contact_id): self
    {
        $this->contact_id = $contact_id;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduled_at;
    }

    public function setScheduledAt(?\DateTimeInterface $scheduled_at): self
    {
        $this->scheduled_at = $scheduled_at;
        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sent_at;
    }

    public function setSentAt(?\DateTimeInterface $sent_at): self
    {
        $this->sent_at = $sent_at;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }
}
