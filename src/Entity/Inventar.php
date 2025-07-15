<?php

namespace Prolyfix\ProcurementBundle\Entity;

use App\Entity\TimeData;
use Doctrine\DBAL\Types\Types;
use Prolyfix\ProcurementBundle\Repository\InventarRepository;
use Doctrine\ORM\Mapping as ORM;
use Prolyfix\ProcurementBundle\Entity\Product;

#[ORM\Entity(repositoryClass: InventarRepository::class)]
class Inventar extends TimeData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventars')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;


    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $expirationDate = null;


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

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }


    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTime $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

}
