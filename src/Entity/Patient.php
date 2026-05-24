<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'patients')]
#[ORM\UniqueConstraint(name: 'uniq_patient_email', columns: ['email'])]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 150)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 150)]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'string', length: 32)]
    private string $phone;

    #[ORM\Column(type: 'date')]
    private \DateTime $dob;

    #[ORM\Column(type: 'string', length: 255)]
    private string $passwordHash;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;
 
    #[ORM\Column(type: 'boolean')]
    private bool $emailVerified = false;
 
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $verificationToken = null;
 
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $resetToken = null;
 
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $resetTokenExpiresAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $v): void { $this->firstName = $v; }
    public function getLastName(): string { return $this->lastName; }
    public function setLastName(string $v): void { $this->lastName = $v; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): void { $this->email = $v; }
    public function getPhone(): string { return $this->phone; }
    public function setPhone(string $v): void { $this->phone = $v; }
    public function getDob(): \DateTime { return $this->dob; }
    public function setDob(\DateTime $d): void { $this->dob = $d; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $v): void { $this->passwordHash = $v; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function isEmailVerified(): bool { return $this->emailVerified; }
    public function setEmailVerified(bool $v): void { $this->emailVerified = $v; }
    public function getVerificationToken(): ?string { return $this->verificationToken; }
    public function setVerificationToken(?string $t): void { $this->verificationToken = $t; }
    public function getResetToken(): ?string { return $this->resetToken; }
    public function setResetToken(?string $t): void { $this->resetToken = $t; }
    public function getResetTokenExpiresAt(): ?\DateTime { return $this->resetTokenExpiresAt; }
    public function setResetTokenExpiresAt(?\DateTime $d): void { $this->resetTokenExpiresAt = $d; }
}
