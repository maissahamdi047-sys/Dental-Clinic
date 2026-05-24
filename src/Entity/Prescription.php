<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'prescriptions')]
class Prescription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $patientEmail;

    #[ORM\Column(type: 'string', length: 255)]
    private string $filename;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $sentAt;

    public function __construct()
    {
        $this->sentAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getPatientEmail(): string { return $this->patientEmail; }
    public function setPatientEmail(string $email): void { $this->patientEmail = $email; }
    public function getFilename(): string { return $this->filename; }
    public function setFilename(string $name): void { $this->filename = $name; }
    public function getSentAt(): \DateTimeImmutable { return $this->sentAt; }
}

