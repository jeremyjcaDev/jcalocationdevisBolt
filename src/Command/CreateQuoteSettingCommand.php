<?php

namespace Jca\JcaLocationdevis\Command;

class CreateQuoteSettingCommand
{
    private $validityHours;
    private $quoteNumberPrefix;
    private $quoteNumberYearFormat;
    private $quoteNumberSeparator;
    private $quoteNumberPadding;

    public function __construct(
        int $validityHours,
        string $quoteNumberPrefix,
        string $quoteNumberYearFormat,
        string $quoteNumberSeparator,
        int $quoteNumberPadding
    ) {
        $this->validityHours = $validityHours;
        $this->quoteNumberPrefix = $quoteNumberPrefix;
        $this->quoteNumberYearFormat = $quoteNumberYearFormat;
        $this->quoteNumberSeparator = $quoteNumberSeparator;
        $this->quoteNumberPadding = $quoteNumberPadding;
    }

    public function getValidityHours(): int
    {
        return $this->validityHours;
    }

    public function getQuoteNumberPrefix(): string
    {
        return $this->quoteNumberPrefix;
    }

    public function getQuoteNumberYearFormat(): string
    {
        return $this->quoteNumberYearFormat;
    }

    public function getQuoteNumberSeparator(): string
    {
        return $this->quoteNumberSeparator;
    }

    public function getQuoteNumberPadding(): int
    {
        return $this->quoteNumberPadding;
    }
}
