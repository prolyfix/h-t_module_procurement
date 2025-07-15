<?php

namespace Prolyfix\ProcurementBundle\Entity;

use App\Entity\TimeData;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Prolyfix\ProcurementBundle\Entity\Product;
use Prolyfix\ProcurementBundle\Repository\ReceiptRepository;

#[ORM\Entity(repositoryClass: ReceiptRepository::class)]
class Receipt extends TimeData
{
    use \App\Entity\Trait\AvatarTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $singleProduct = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $state = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'receipts')]
    private Collection $products;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    public function __construct()
    {
        parent::__construct();
        $this->products = new ArrayCollection();
        $this->state = 'Pending';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->products->removeElement($product);

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): static
    {
        $this->total = $total;

        return $this;
    }
}
