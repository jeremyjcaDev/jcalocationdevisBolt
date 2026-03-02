<?php

namespace Jca\JcaLocationdevis\Command;

class DeleteProductRentalAvailabilityCommand
{
    private $idProductRentalAvailability;

    public function __construct(int $idProductRentalAvailability)
    {
        $this->idProductRentalAvailability = $idProductRentalAvailability;
    }

    public function getIdProductRentalAvailability(): int
    {
        return $this->idProductRentalAvailability;
    }
}
