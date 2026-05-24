<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'appointments')]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $patient;

    #[ORM\Column(type: 'string', length: 255)]
    private string $service;


    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $scheduledAt;

    #[ORM\Column(type: 'string', length: 255)]
    private string $doctor;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'Pending Approval';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $patientEmail = null;

    public function getId(): ?int { return $this->id; }
    public function getPatient(): string { return $this->patient; }
    public function setPatient(string $patient): void { $this->patient = $patient; }
    public function getService(): string { return $this->service; }
    public function setService(string $service): void { $this->service = $service; }
    public function getScheduledAt(): \DateTimeImmutable { return $this->scheduledAt; }
    public function setScheduledAt(\DateTimeImmutable $at): void { $this->scheduledAt = $at; }
    public function getDoctor(): string { return $this->doctor; }
    public function setDoctor(string $doctor): void { $this->doctor = $doctor; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function getPatientEmail(): ?string { return $this->patientEmail; }
    public function setPatientEmail(?string $email): void { $this->patientEmail = $email; }
}
