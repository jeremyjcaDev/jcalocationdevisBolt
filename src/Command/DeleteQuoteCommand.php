<?php

namespace Jca\JcaLocationdevis\Command;

class DeleteQuoteCommand
{
    private $quoteId;

    public function __construct(int $quoteId)
    {
        $this->quoteId = $quoteId;
    }

    public function getQuoteId(): int
    {
        return $this->quoteId;
    }
}
