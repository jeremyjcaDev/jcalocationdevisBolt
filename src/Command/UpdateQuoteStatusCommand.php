<?php

namespace Jca\JcaLocationdevis\Command;

class UpdateQuoteStatusCommand
{
    private $quoteId;
    private $newStatus;

    public function __construct(int $quoteId, string $newStatus)
    {
        $this->quoteId = $quoteId;
        $this->newStatus = $newStatus;
    }

    public function getQuoteId(): int
    {
        return $this->quoteId;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }
}
