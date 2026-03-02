<?php

namespace Jca\JcaLocationdevis\Command;

class UpdateRentalConfigurationCommand
{
    private $idRentalConfiguration;
    private $priceMin;
    private $priceMax;
    private $duration36Months;
    private $duration60Months;
    private $sortOrder;

    public function __construct(
        int $idRentalConfiguration,
        ?float $priceMin = null,
        ?float $priceMax = null,
        ?float $duration36Months = null,
        ?float $duration60Months = null,
        ?int $sortOrder = null
    ) {
        $this->idRentalConfiguration = $idRentalConfiguration;
        $this->priceMin = $priceMin;
        $this->priceMax = $priceMax;
        $this->duration36Months = $duration36Months;
        $this->duration60Months = $duration60Months;
        $this->sortOrder = $sortOrder;
    }

    public function getIdRentalConfiguration(): int
    {
        return $this->idRentalConfiguration;
    }

    public function getPriceMin(): ?float
    {
        return $this->priceMin;
    }

    public function getPriceMax(): ?float
    {
        return $this->priceMax;
    }

    public function getDuration36Months(): ?float
    {
        return $this->duration36Months;
    }

    public function getDuration60Months(): ?float
    {
        return $this->duration60Months;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }
}
