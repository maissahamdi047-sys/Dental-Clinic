<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Appointment::class)]
    #[ORM\JoinColumn(name: 'appointment_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Appointment $appointment = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount = '0.00';

    #[ORM\Column(type: 'string', length: 8)]
    private string $currency = 'TND';

    #[ORM\Column(type: 'string', length: 32)]
    private string $method = 'Cash';

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'Pending';

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getAppointment(): ?Appointment { return $this->appointment; }
    public function setAppointment(?Appointment $appointment): void { $this->appointment = $appointment; }
    public function getAmount(): string { return $this->amount; }
    public function setAmount(string $amount): void { $this->amount = $amount; }
    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): void { $this->currency = $currency; }
    public function getMethod(): string { return $this->method; }
    public function setMethod(string $method): void { $this->method = $method; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function getReference(): ?string { return $this->reference; }
    public function setReference(?string $reference): void { $this->reference = $reference; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

