<?php

namespace Prolyfix\ProcurementBundle\Entity;

use App\Entity\TimeData;
use Prolyfix\ProcurementBundle\Repository\OrderLineRepository;
use Doctrine\ORM\Mapping as ORM;
use Prolyfix\ProcurementBundle\Entity\Order;
use Prolyfix\ProcurementBundle\Entity\Product;

#[ORM\Entity(repositoryClass: OrderLineRepository::class)]
class OrderLine extends TimeData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderLines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $procurementOrder = null;

    #[ORM\ManyToOne(inversedBy: 'orderLines')]
    private ?Product $product = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $orderLine = null;

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

    public function __contruct()
    {
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProcurementOrder(): ?Order
    {
        return $this->procurementOrder;
    }

    public function setProcurementOrder(?Order $procurementOrder): static
    {
        $this->procurementOrder = $procurementOrder;

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

    public function getOrderLine(): ?string
    {
        return $this->orderLine;
    }

    public function setOrderLine(?string $orderLine): static
    {
        $this->orderLine = $orderLine;

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
        return $this->getQuantity() * $this->getGrossPrice();
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
