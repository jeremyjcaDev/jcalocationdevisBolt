<?php

namespace Jca\JcaLocationdevis\Query;

class ListQuotesQuery
{
    private $status;

    public function __construct(?string $status = null)
    {
        $this->status = $status;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }
}
