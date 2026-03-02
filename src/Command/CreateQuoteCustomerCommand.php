<?php

namespace Jca\JcaLocationdevis\Command;

class CreateQuoteCustomerCommand
{
    private $email;
    private $name;
    private $phone;
    private $idQuote;
    private $idCustomerPrestashop;

    public function __construct(
        string $email,
        ?string $name = null,
        ?string $phone = null,
        ?int $idQuote = null,
        ?int $idCustomerPrestashop = null
    ) {
        $this->email = $email;
        $this->name = $name;
        $this->phone = $phone;
        $this->idQuote = $idQuote;
        $this->idCustomerPrestashop = $idCustomerPrestashop;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getIdQuote(): ?int
    {
        return $this->idQuote;
    }

    public function getIdCustomerPrestashop(): ?int
    {
        return $this->idCustomerPrestashop;
    }
}
