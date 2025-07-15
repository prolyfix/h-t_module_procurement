<?php

namespace Prolyfix\ProcurementBundle\Entity;

use App\Entity\TimeData;
use Prolyfix\ProcurementBundle\Repository\ShoppingListRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Prolyfix\ProcurementBundle\Entity\Product;

#[ORM\Entity(repositoryClass: ShoppingListRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['module_configuration_value:read']],
    denormalizationContext: ['groups' => ['module_configuration_value:write']],
)]
class ShoppingList extends TimeData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['module_configuration_value:write'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'shoppingLists')]
    private ?Product $product = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['module_configuration_value:write'])]
    private ?float $quantity = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['module_configuration_value:write'])]
    private ?string $state = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['module_configuration_value:write'])]
    private ?bool $isOrdered = null;

    #[ORM\ManyToOne(inversedBy: 'shoppingLists',cascade: ['persist'])]
    private ?Order $procurementOrder = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

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

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): static
    {
        $this->quantity = $quantity;

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

    public function isOrdered(): ?bool
    {
        return $this->isOrdered;
    }

    public function setIsOrdered(?bool $isOrdered): static
    {
        $this->isOrdered = $isOrdered;

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
}
