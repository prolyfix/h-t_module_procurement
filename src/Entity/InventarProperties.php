<?php

namespace Prolyfix\ProcurementBundle\Entity;

use Prolyfix\ProcurementBundle\Repository\InventarPropertiesRepository;
use Doctrine\ORM\Mapping as ORM;
use Prolyfix\ProcurementBundle\Entity\Product;

#[ORM\Entity(repositoryClass: InventarPropertiesRepository::class)]
class InventarProperties
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(nullable: true)]
    private ?float $minimalQuantity = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hasDateOfValidity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getMinimalQuantity(): ?float
    {
        return $this->minimalQuantity;
    }

    public function setMinimalQuantity(?float $minimalQuantity): static
    {
        $this->minimalQuantity = $minimalQuantity;

        return $this;
    }

    public function hasDateOfValidity(): ?bool
    {
        return $this->hasDateOfValidity;
    }

    public function setHasDateOfValidity(?bool $hasDateOfValidity): static
    {
        $this->hasDateOfValidity = $hasDateOfValidity;

        return $this;
    }
}
