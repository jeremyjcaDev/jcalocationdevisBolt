<?php

namespace Jca\JcaLocationdevis\Command;

class CreateRentalConfigurationCommand
{
    private $priceMin;
    private $priceMax;
    private $duration36Months;
    private $duration60Months;
    private $sortOrder;

    public function __construct(
        float $priceMin,
        float $priceMax,
        float $duration36Months,
        float $duration60Months,
        int $sortOrder
    ) {
        $this->priceMin = $priceMin;
        $this->priceMax = $priceMax;
        $this->duration36Months = $duration36Months;
        $this->duration60Months = $duration60Months;
        $this->sortOrder = $sortOrder;
    }

    public function getPriceMin(): float
    {
        return $this->priceMin;
    }

    public function getPriceMax(): float
    {
        return $this->priceMax;
    }

    public function getDuration36Months(): float
    {
        return $this->duration36Months;
    }

    public function getDuration60Months(): float
    {
        return $this->duration60Months;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
}
