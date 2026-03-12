<?php

namespace Prolyfix\ProcurementBundle\Entity;

use ApiPlatform\Metadata\ApiResource;
use Prolyfix\HolidayAndTime\Entity\Commentable;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Prolyfix\ProcurementBundle\Repository\DeliverySlipRepository;
use Doctrine\ORM\Mapping as ORM;
use Prolyfix\CrmBundle\Entity\ThirdParty;

#[ORM\Entity(repositoryClass: DeliverySlipRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['delivery_slip:read']],
    denormalizationContext: ['groups' => ['delivery_slip:write']],
)]
class DeliverySlip extends Commentable
{
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $deliverySlipId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deliveryDate = null;

    #[ORM\ManyToOne(inversedBy: 'deliverySlips')]
    private ?ThirdParty $thirdParty = null;

    #[ORM\ManyToOne(inversedBy: 'deliverySlips')]
    private ?Order $procurementOrder = null;

    #[ORM\ManyToOne(inversedBy: 'deliverySlips')]
    private ?Invoice $invoice = null;

    /**
     * @var Collection<int, DeliverySlipLine>
     */
    #[ORM\OneToMany(targetEntity: DeliverySlipLine::class, mappedBy: 'deliverySlip', cascade: ['persist', 'remove'])]
    private Collection $deliverySlipLines;

    public function __construct()
    {
        parent::__construct();
        $this->deliverySlipLines = new ArrayCollection();
        $this->state = 'pending';
    }

    public function getDeliverySlipId(): ?string
    {
        return $this->deliverySlipId;
    }

    public function setDeliverySlipId(?string $deliverySlipId): static
    {
        $this->deliverySlipId = $deliverySlipId;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

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

    public function getThirdParty(): ?ThirdParty
    {
        return $this->thirdParty;
    }

    public function setThirdParty(?ThirdParty $thirdParty): static
    {
        $this->thirdParty = $thirdParty;

        return $this;
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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @return Collection<int, DeliverySlipLine>
     */
    public function getDeliverySlipLines(): Collection
    {
        return $this->deliverySlipLines;
    }

    public function addDeliverySlipLine(DeliverySlipLine $deliverySlipLine): static
    {
        if (!$this->deliverySlipLines->contains($deliverySlipLine)) {
            $this->deliverySlipLines->add($deliverySlipLine);
            $deliverySlipLine->setDeliverySlip($this);
        }

        return $this;
    }

    public function removeDeliverySlipLine(DeliverySlipLine $deliverySlipLine): static
    {
        if ($this->deliverySlipLines->removeElement($deliverySlipLine)) {
            if ($deliverySlipLine->getDeliverySlip() === $this) {
                $deliverySlipLine->setDeliverySlip(null);
            }
        }

        return $this;
    }

    public function getPrice(): float
    {
        $price = 0;
        foreach ($this->deliverySlipLines as $line) {
            $price += $line->getPrice();
        }

        return $price;
    }

    public function __toString(): string
    {
        return $this->deliverySlipId ?? 'No Delivery Slip ID';
    }
}
