<?php

namespace Jca\JcaLocationdevis\Command;

class CreateQuoteCommand
{
    private $quoteType;
    private $customerName;
    private $customerEmail;
    private $customerPhone;
    private $status;
    private $validUntil;
    private $items;

    public function __construct(
        string $quoteType = 'standard',
        ?string $customerName = null,
        ?string $customerEmail = null,
        ?string $customerPhone = null,
        string $status = 'draft',
        ?string $validUntil = null,
        array $items = []
    ) {
        $this->quoteType = $quoteType;
        $this->customerName = $customerName;
        $this->customerEmail = $customerEmail;
        $this->customerPhone = $customerPhone;
        $this->status = $status;
        $this->validUntil = $validUntil;
        $this->items = $items;
    }

    public function getQuoteType(): string
    {
        return $this->quoteType;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getValidUntil(): ?string
    {
        return $this->validUntil;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
