<?php

namespace Jca\JcaLocationdevis\Command;

class DeleteRentalConfigurationCommand
{
    private $idRentalConfiguration;

    public function __construct(int $idRentalConfiguration)
    {
        $this->idRentalConfiguration = $idRentalConfiguration;
    }

    public function getIdRentalConfiguration(): int
    {
        return $this->idRentalConfiguration;
    }
}
