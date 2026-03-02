<?php

namespace Jca\JcaLocationdevis\Query;

class GetQuoteWithItemsQuery
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
