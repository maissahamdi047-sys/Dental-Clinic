<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'services')]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $slug;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column(type: 'integer')]
    private int $durationMin = 60;

    #[ORM\Column(type: 'string', length: 64, options: ['default' => 'General Dentistry'])]
    private string $category = 'General Dentistry';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageUrl = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $v): void { $this->name = $v; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $v): void { $this->slug = $v; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $v): void { $this->description = $v; }
    public function getPrice(): string { return $this->price; }
    public function setPrice(string $v): void { $this->price = $v; }
    public function getDurationMin(): int { return $this->durationMin; }
    public function setDurationMin(int $v): void { $this->durationMin = $v; }
    public function getCategory(): string { return $this->category; }
    public function setCategory(string $v): void { $this->category = $v; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $v): void { $this->imageUrl = $v; }
}
