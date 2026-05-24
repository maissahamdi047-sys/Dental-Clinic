<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'reviews')]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 150)]
    private string $author;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'smallint')]
    private int $rating = 5;

    #[ORM\Column(type: 'text')]
    private string $comment;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getAuthor(): string { return $this->author; }
    public function setAuthor(string $v): void { $this->author = $v; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $v): void { $this->email = $v; }
    public function getRating(): int { return $this->rating; }
    public function setRating(int $v): void { $this->rating = $v; }
    public function getComment(): string { return $this->comment; }
    public function setComment(string $v): void { $this->comment = $v; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

