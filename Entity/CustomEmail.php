<?php

namespace MauticPlugin\CustomEmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="custom_email")
 */
class CustomEmail
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $startDate;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $endDate;

    /** @ORM\Column(type="integer", nullable=true) */
    private $dailyLimit;

    /** @ORM\Column(type="float", nullable=true) */
    private $dailyIncrement;

    /** @ORM\Column(type="integer", nullable=true) */
    private $delay;

    /** @ORM\Column(type="string", length=10, nullable=true) */
    private $delayUnit;

    // Getters and Setters
    public function getId(): ?int { return $this->id; }
    public function getStartDate(): ?\DateTimeInterface { return $this->startDate; }
    public function setStartDate(?\DateTimeInterface $startDate): self { $this->startDate = $startDate; return $this; }

    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
    public function setEndDate(?\DateTimeInterface $endDate): self { $this->endDate = $endDate; return $this; }

    public function getDailyLimit(): ?int { return $this->dailyLimit; }
    public function setDailyLimit(?int $dailyLimit): self { $this->dailyLimit = $dailyLimit; return $this; }

    public function getDailyIncrement(): ?float { return $this->dailyIncrement; }
    public function setDailyIncrement(?float $dailyIncrement): self { $this->dailyIncrement = $dailyIncrement; return $this; }

    public function getDelay(): ?int { return $this->delay; }
    public function setDelay(?int $delay): self { $this->delay = $delay; return $this; }

    public function getDelayUnit(): ?string { return $this->delayUnit; }
    public function setDelayUnit(?string $delayUnit): self { $this->delayUnit = $delayUnit; return $this; }
}
