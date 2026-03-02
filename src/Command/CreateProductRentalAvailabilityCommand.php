<?php

namespace Jca\JcaLocationdevis\Command;

class CreateProductRentalAvailabilityCommand
{
    private $idProduct;
    private $productReference;
    private $productName;
    private $productPrice;

    public function __construct(
        int $idProduct,
        string $productReference,
        string $productName,
        float $productPrice
    ) {
        $this->idProduct = $idProduct;
        $this->productReference = $productReference;
        $this->productName = $productName;
        $this->productPrice = $productPrice;
    }

    public function getIdProduct(): int
    {
        return $this->idProduct;
    }

    public function getProductReference(): string
    {
        return $this->productReference;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getProductPrice(): float
    {
        return $this->productPrice;
    }
}
