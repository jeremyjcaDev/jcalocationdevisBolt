<?php

namespace Jca\JcaLocationdevis\Command;

class UpdateProductRentalAvailabilityCommand
{
    private $idProductRentalAvailability;
    private $productReference;
    private $productName;
    private $productPrice;
    private $rentalEnabled;
    private $duration36Enabled;
    private $duration60Enabled;

    public function __construct(
        int $idProductRentalAvailability,
        ?string $productReference = null,
        ?string $productName = null,
        ?float $productPrice = null,
        ?bool $rentalEnabled = null,
        ?bool $duration36Enabled = null,
        ?bool $duration60Enabled = null
    ) {
        $this->idProductRentalAvailability = $idProductRentalAvailability;
        $this->productReference = $productReference;
        $this->productName = $productName;
        $this->productPrice = $productPrice;
        $this->rentalEnabled = $rentalEnabled;
        $this->duration36Enabled = $duration36Enabled;
        $this->duration60Enabled = $duration60Enabled;
    }

    public function getIdProductRentalAvailability(): int
    {
        return $this->idProductRentalAvailability;
    }

    public function getProductReference(): ?string
    {
        return $this->productReference;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function getProductPrice(): ?float
    {
        return $this->productPrice;
    }

    public function getRentalEnabled(): ?bool
    {
        return $this->rentalEnabled;
    }

    public function getDuration36Enabled(): ?bool
    {
        return $this->duration36Enabled;
    }

    public function getDuration60Enabled(): ?bool
    {
        return $this->duration60Enabled;
    }
}
