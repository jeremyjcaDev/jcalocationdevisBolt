<?php

namespace Jca\JcaLocationdevis\Command;

class UpdateQuoteCommand
{
    private $quoteId;
    private $customerName;
    private $customerEmail;
    private $customerPhone;

    public function __construct(
        int $quoteId,
        ?string $customerName = null,
        ?string $customerEmail = null,
        ?string $customerPhone = null
    ) {
        $this->quoteId = $quoteId;
        $this->customerName = $customerName;
        $this->customerEmail = $customerEmail;
        $this->customerPhone = $customerPhone;
    }

    public function getQuoteId(): int
    {
        return $this->quoteId;
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
}
