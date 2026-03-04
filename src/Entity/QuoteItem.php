<?php

namespace Jca\JcaLocationdevis\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jca\JcaLocationdevis\Repository\QuoteItemRepository;

/**
 * @ORM\Entity(repositoryClass=QuoteItemRepository::class)
 * @ORM\Table(name="ps_jca_quote_items")
 */
class QuoteItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id_quote_item", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="id_quote", type="integer")
     */
    private $idQuote;

    /**
     * @ORM\Column(name="item_type", type="string", length=10)
     */
    private $itemType = 'product';

    /**
     * @ORM\Column(name="id_product", type="integer")
     */
    private $idProduct;

    /**
     * @ORM\Column(name="product_reference", type="string", length=100, nullable=true)
     */
    private $productReference;

    /**
     * @ORM\Column(name="product_name", type="string", length=255)
     */
    private $productName;

    /**
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity = 1;

    /**
     * @ORM\Column(name="id_rental_configuration", type="integer", nullable=true)
     */
    private $idRentalConfiguration;

    /**
     * @ORM\Column(name="is_rental", type="boolean")
     */
    private $isRental;

    /**
     * @ORM\Column(name="duration_months", type="integer", nullable=true)
     */
    private $durationMonths;

    /**
     * @ORM\Column(name="rate_percentage", type="decimal", precision=5, scale=2, nullable=true)
     */
    private $ratePercentage;

    /**
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(name="original_price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $originalPrice;

    /**
     * @ORM\Column(name="discount_percentage", type="decimal", precision=5, scale=2, nullable=true)
     */
    private $discountPercentage;

    /**
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $dateAdd;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdQuote(): ?int
    {
        return $this->idQuote;
    }

    public function setIdQuote(int $idQuote): self
    {
        $this->idQuote = $idQuote;
        return $this;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function setItemType(string $itemType): self
    {
        $this->itemType = $itemType;
        return $this;
    }

    public function getIdProduct(): ?int
    {
        return $this->idProduct;
    }

    public function setIdProduct(int $idProduct): self
    {
        $this->idProduct = $idProduct;
        return $this;
    }

    public function getProductReference(): ?string
    {
        return $this->productReference;
    }

    public function setProductReference(?string $productReference): self
    {
        $this->productReference = $productReference;
        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getIdRentalConfiguration(): ?int
    {
        return $this->idRentalConfiguration;
    }

    public function setIdRentalConfiguration(?int $idRentalConfiguration): self
    {
        $this->idRentalConfiguration = $idRentalConfiguration;
        return $this;
    }

    public function getIsRental(): bool
    {
        return $this->isRental;
    }

    public function setIsRental(bool $isRental): self
    {
        $this->isRental = $isRental;
        return $this;
    }

    public function getDurationMonths(): ?int
    {
        return $this->durationMonths;
    }

    public function setDurationMonths(?int $durationMonths): self
    {
        $this->durationMonths = $durationMonths;
        return $this;
    }

    public function getRatePercentage(): ?float
    {
        return $this->ratePercentage;
    }

    public function setRatePercentage(?float $ratePercentage): self
    {
        $this->ratePercentage = $ratePercentage;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getOriginalPrice(): ?float
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(?float $originalPrice): self
    {
        $this->originalPrice = $originalPrice;
        return $this;
    }

    public function getDiscountPercentage(): ?float
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(?float $discountPercentage): self
    {
        $this->discountPercentage = $discountPercentage;
        return $this;
    }

    public function getDateAdd(): \DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;
        return $this;
    }
}
