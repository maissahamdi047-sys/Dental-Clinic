<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'shop_products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 200)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 120, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(type: 'float')]
    private float $price;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $promoPrice = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPromo = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $v): void { $this->name = $v; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): void { $this->description = $v; }
    public function getBrand(): ?string { return $this->brand; }
    public function setBrand(?string $v): void { $this->brand = $v; }
    public function getPrice(): float { return $this->price; }
    public function setPrice(float $v): void { $this->price = $v; }
    public function getPromoPrice(): ?float { return $this->promoPrice; }
    public function setPromoPrice(?float $v): void { $this->promoPrice = $v; }
    public function isPromo(): bool { return $this->isPromo; }
    public function setIsPromo(bool $v): void { $this->isPromo = $v; }
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $v): void { $this->image = $v; }
    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $c): void { $this->category = $c; }
}
