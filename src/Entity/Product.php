<?php

namespace Prolyfix\ProcurementBundle\Entity;

use App\Attribute\SearchableEntity;
use App\Attribute\SearchableField;
use App\Entity\Commentable;
use Prolyfix\ProcurementBundle\Entity\Inventar;
use Prolyfix\ProcurementBundle\Entity\OrderLine;
use App\Entity\TimeData;
use App\Entity\Trait\AvatarTrait;
use App\Entity\Trait\CommentsTrait;
use App\Entity\Trait\MediaTrait;
use Prolyfix\ProcurementBundle\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[SearchableEntity(controller: 'Prolyfix\ProcurementBundle\Controller\ProductCrudController')]
class Product extends Commentable
{
    use AvatarTrait;


    #[ORM\Column(length: 255)]
    #[SearchableField]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;



    /**
     * @var Collection<int, Order>
     */
    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'products')]
    private Collection $orders;

    /**
     * @var Collection<int, Receipt>
     */
    #[ORM\ManyToMany(targetEntity: Receipt::class, mappedBy: 'products')]
    private Collection $receipts;

    /**
     * @var Collection<int, OrderLine>
     */
    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'product')]
    private Collection $orderLines;

    /**
     * @var Collection<int, Inventar>
     */
    #[ORM\OneToMany(targetEntity: Inventar::class, mappedBy: 'product')]
    private Collection $inventars;

    #[ORM\Column(nullable: true)]
    private ?bool $isSprechstundenbedarf = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hasExpirationDate = null;

    #[ORM\Column]
    private ?float $minimalQuantity = null;


    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->receipts = new ArrayCollection();
        $this->orderLines = new ArrayCollection();
        $this->inventars = new ArrayCollection();
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }



    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->addProduct($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            $order->removeProduct($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Receipt>
     */
    public function getReceipts(): Collection
    {
        return $this->receipts;
    }

    public function addReceipt(Receipt $receipt): static
    {
        if (!$this->receipts->contains($receipt)) {
            $this->receipts->add($receipt);
            $receipt->addProduct($this);
        }

        return $this;
    }

    public function removeReceipt(Receipt $receipt): static
    {
        if ($this->receipts->removeElement($receipt)) {
            $receipt->removeProduct($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderLine>
     */
    public function getOrderLines(): Collection
    {
        return $this->orderLines;
    }

    public function addOrderLine(OrderLine $orderLine): static
    {
        if (!$this->orderLines->contains($orderLine)) {
            $this->orderLines->add($orderLine);
            $orderLine->setProduct($this);
        }

        return $this;
    }

    public function removeOrderLine(OrderLine $orderLine): static
    {
        if ($this->orderLines->removeElement($orderLine)) {
            // set the owning side to null (unless already changed)
            if ($orderLine->getProduct() === $this) {
                $orderLine->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Inventar>
     */
    public function getInventars(): Collection
    {
        return $this->inventars;
    }

    public function addInventar(Inventar $inventar): static
    {
        if (!$this->inventars->contains($inventar)) {
            $this->inventars->add($inventar);
            $inventar->setProduct($this);
        }

        return $this;
    }

    public function removeInventar(Inventar $inventar): static
    {
        if ($this->inventars->removeElement($inventar)) {
            // set the owning side to null (unless already changed)
            if ($inventar->getProduct() === $this) {
                $inventar->setProduct(null);
            }
        }

        return $this;
    }

    public function isSprechstundenbedarf(): ?bool
    {
        return $this->isSprechstundenbedarf??false;
    }

    public function setIsSprechstundenbedarf(?bool $isSprechstundenbedarf): static
    {
        $this->isSprechstundenbedarf = $isSprechstundenbedarf;

        return $this;
    }

    public function hasExpirationDate(): ?bool
    {
        return $this->hasExpirationDate;
    }

    public function setHasExpirationDate(?bool $hasExpirationDate): static
    {
        $this->hasExpirationDate = $hasExpirationDate;

        return $this;
    }

    public function getMinimalQuantity(): ?float
    {
        return $this->minimalQuantity;
    }

    public function setMinimalQuantity(float $minimalQuantity): static
    {
        $this->minimalQuantity = $minimalQuantity;

        return $this;
    }
}
