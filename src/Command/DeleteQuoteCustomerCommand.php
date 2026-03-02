<?php

namespace Jca\JcaLocationdevis\Command;

class DeleteQuoteCustomerCommand
{
    private $idCustomer;

    public function __construct(int $idCustomer)
    {
        $this->idCustomer = $idCustomer;
    }

    public function getIdCustomer(): int
    {
        return $this->idCustomer;
    }
}
