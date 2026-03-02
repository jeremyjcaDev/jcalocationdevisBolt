<?php

namespace Jca\JcaLocationdevis\Entity;
use Jca\JcaLocationdevis\Repository\ProductRentalAvailabilityRepository;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRentalAvailabilityRepository::class)
 * @ORM\Table(name="ps_jca_product_rental_availability")
 */
class ProductRentalAvailability
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id_product_rental_availability", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="id_product", type="integer")
     */
    private $idProduct;

    /**
     * @ORM\Column(name="product_reference", type="string", length=100)
     */
    private $productReference;

    /**
     * @ORM\Column(name="product_name", type="string", length=255)
     */
    private $productName;

    /**
     * @ORM\Column(name="product_price", type="decimal", precision=10, scale=2)
     */
    private $productPrice;

    /**
     * @ORM\Column(name="rental_enabled", type="boolean")
     */
    private $rentalEnabled;

    /**
     * @ORM\Column(name="duration_36_enabled", type="boolean")
     */
    private $duration36Enabled;

    /**
     * @ORM\Column(name="duration_60_enabled", type="boolean")
     */
    private $duration60Enabled;

    /**
     * @ORM\Column(name="id_rental_configuration", type="integer", nullable=true)
     */
    private $idRentalConfiguration;

    /**
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $dateAdd;

    /**
     * @ORM\Column(name="date_upd", type="datetime")
     */
    private $dateUpd;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdProduct(): int
    {
        return $this->idProduct;
    }

    public function setIdProduct(int $idProduct): self
    {
        $this->idProduct = $idProduct;
        return $this;
    }

    public function getProductReference(): string
    {
        return $this->productReference;
    }

    public function setProductReference(string $productReference): self
    {
        $this->productReference = $productReference;
        return $this;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
        return $this;
    }

    public function getProductPrice(): float
    {
        return $this->productPrice;
    }

    public function setProductPrice(float $productPrice): self
    {
        $this->productPrice = $productPrice;
        return $this;
    }

    public function getRentalEnabled(): bool
    {
        return $this->rentalEnabled;
    }

    public function setRentalEnabled(bool $rentalEnabled): self
    {
        $this->rentalEnabled = $rentalEnabled;
        return $this;
    }

    public function getDuration36Enabled(): bool
    {
        return $this->duration36Enabled;
    }

    public function setDuration36Enabled(bool $duration36Enabled): self
    {
        $this->duration36Enabled = $duration36Enabled;
        return $this;
    }

    public function getDuration60Enabled(): bool
    {
        return $this->duration60Enabled;
    }

    public function setDuration60Enabled(bool $duration60Enabled): self
    {
        $this->duration60Enabled = $duration60Enabled;
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

    public function getDateAdd(): \DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;
        return $this;
    }

    public function getDateUpd(): \DateTimeInterface
    {
        return $this->dateUpd;
    }

    public function setDateUpd(\DateTimeInterface $dateUpd): self
    {
        $this->dateUpd = $dateUpd;
        return $this;
    }
}