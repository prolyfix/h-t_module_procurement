<?php

namespace Prolyfix\ProcurementBundle\Entity;

use Prolyfix\HolidayAndTime\Entity\TimeData;
use Prolyfix\ProcurementBundle\Repository\DeliverySlipLineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliverySlipLineRepository::class)]
class DeliverySlipLine extends TimeData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'deliverySlipLines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DeliverySlip $deliverySlip = null;

    #[ORM\ManyToOne]
    private ?Product $product = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantity = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $measure = null;

    #[ORM\Column(nullable: true)]
    private ?float $grossPrice = null;

    #[ORM\Column(nullable: true)]
    private ?float $vat = null;

    #[ORM\Column(nullable: true)]
    private ?float $netPrice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeliverySlip(): ?DeliverySlip
    {
        return $this->deliverySlip;
    }

    public function setDeliverySlip(?DeliverySlip $deliverySlip): static
    {
        $this->deliverySlip = $deliverySlip;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getMeasure(): ?string
    {
        return $this->measure;
    }

    public function setMeasure(?string $measure): static
    {
        $this->measure = $measure;

        return $this;
    }

    public function getGrossPrice(): ?float
    {
        return $this->grossPrice;
    }

    public function setGrossPrice(?float $grossPrice): static
    {
        $this->grossPrice = $grossPrice;

        return $this;
    }

    public function getPrice(): float
    {
        return ($this->quantity ?? 0) * ($this->grossPrice ?? 0);
    }

    public function getVat(): ?float
    {
        return $this->vat;
    }

    public function setVat(?float $vat): static
    {
        $this->vat = $vat;

        return $this;
    }

    public function getNetPrice(): ?float
    {
        return $this->netPrice;
    }

    public function setNetPrice(?float $netPrice): static
    {
        $this->netPrice = $netPrice;

        return $this;
    }
}
