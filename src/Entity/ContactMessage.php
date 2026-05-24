<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contact_messages')]
class ContactMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 150)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $subject;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $response = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $v): void { $this->name = $v; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): void { $this->email = $v; }
    public function getSubject(): string { return $this->subject; }
    public function setSubject(string $v): void { $this->subject = $v; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $v): void { $this->message = $v; }
    public function getResponse(): ?string { return $this->response; }
    public function setResponse(?string $v): void { 
        $this->response = $v; 
        if ($v) { $this->respondedAt = new \DateTimeImmutable(); }
    }
    public function getRespondedAt(): ?\DateTimeImmutable { return $this->respondedAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

