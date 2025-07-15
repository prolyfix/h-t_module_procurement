<?php

namespace Prolyfix\ProcurementBundle\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Comment;
use App\Entity\Commentable;
use Doctrine\DBAL\Types\Types;
use Prolyfix\ProcurementBundle\Entity\OrderLine;
use App\Entity\TimeData;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Prolyfix\ProcurementBundle\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Prolyfix\CrmBundle\Entity\ThirdParty;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource(
    normalizationContext: ['groups' => ['module_configuration_value:read']],
    denormalizationContext: ['groups' => ['module_configuration_value:write']],
)]
class Order extends Commentable
{

    #[ORM\Column(length: 255)]
    private ?string $state = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?ThirdParty $thirdParty = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $singleProduct = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPaid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isDelivered = null;

    /**
     * @var Collection<int, OrderLine>
     */
    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'procurementOrder', cascade: ['persist','remove'])]
    private Collection $orderLines;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['module_configuration_value:write'])]
    private ?\DateTimeInterface $deliveryDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['module_configuration_value:write'])]
    private ?\DateTimeInterface $paymentDate = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isOrdered = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $orderDate = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['module_configuration_value:write'])]
    private ?string $orderId = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['module_configuration_value:write'])]
    private ?string $trackingId = null;


    /**
     * @var Collection<int, ShoppingList>
     */
    #[ORM\OneToMany(targetEntity: ShoppingList::class, mappedBy: 'procurementOrder', cascade: ['persist'])]
    private Collection $shoppingLists;

    #[ORM\Column(nullable: true)]
    private ?bool $isSprechStundeBedarf = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Invoice $invoice = null;


    public function __construct()
    {
        parent::__construct();
        $this->orderLines = new ArrayCollection();
        $this->state = 'new';
        $this->shoppingLists = new ArrayCollection();
        $this->orderDate = new \DateTime();
    }


    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getThirdParty(): ?ThirdParty
    {
        return $this->thirdParty;
    }

    public function setThirdParty(?ThirdParty $thirdParty): static
    {
        $this->thirdParty = $thirdParty;

        return $this;
    }

    public function getSingleProduct(): ?string
    {
        return $this->singleProduct;
    }

    public function setSingleProduct(?string $singleProduct): static
    {
        $this->singleProduct = $singleProduct;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(?bool $isPaid): static
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function isDelivered(): ?bool
    {
        return $this->isDelivered;
    }

    public function setIsDelivered(?bool $isDelivered): static
    {
        $this->isDelivered = $isDelivered;

        return $this;
    }

    public function getPrice(): float
    {
        $price = 0;
        foreach ($this->orderLines as $orderLine) {
            $price += $orderLine->getPrice();
        }

        return $price;
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
            $orderLine->setProcurementOrder($this);
        }

        return $this;
    }

    public function removeOrderLine(OrderLine $orderLine): static
    {
        if ($this->orderLines->removeElement($orderLine)) {
            // set the owning side to null (unless already changed)
            if ($orderLine->getProcurementOrder() === $this) {
                $orderLine->setProcurementOrder(null);
            }
        }

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?\DateTimeInterface $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): static
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function isOrdered(): ?bool
    {
        return $this->isOrdered;
    }

    public function setIsOrdered(?bool $isOrdered): static
    {
        $this->isOrdered = $isOrdered;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface $orderDate): static
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getTrackingId(): ?string
    {
        return $this->trackingId;
    }

    public function setTrackingId(?string $trackingId): static
    {
        $this->trackingId = $trackingId;

        return $this;
    }


    public function __toString()
    {
        return $this->orderId ?? 'No Order ID';
    }

    /**
     * @return Collection<int, ShoppingList>
     */
    public function getShoppingLists(): Collection
    {
        return $this->shoppingLists;
    }

    public function addShoppingList(ShoppingList $shoppingList): static
    {
        if (!$this->shoppingLists->contains($shoppingList)) {
            $this->shoppingLists->add($shoppingList);
            $shoppingList->setProcurementOrder($this);
        }

        return $this;
    }

    public function removeShoppingList(ShoppingList $shoppingList): static
    {
        if ($this->shoppingLists->removeElement($shoppingList)) {
            // set the owning side to null (unless already changed)
            if ($shoppingList->getProcurementOrder() === $this) {
                $shoppingList->setProcurementOrder(null);
            }
        }

        return $this;
    }

    public function isSprechStundeBedarf(): ?bool
    {
        return $this->isSprechStundeBedarf??false;
    }

    public function setIsSprechStundeBedarf(?bool $isSprechStundeBedarf): static
    {
        $this->isSprechStundeBedarf = $isSprechStundeBedarf;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

}
