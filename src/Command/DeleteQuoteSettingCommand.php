<?php

namespace Jca\JcaLocationdevis\Command;

class DeleteQuoteSettingCommand
{
    private $idQuoteSetting;

    public function __construct(int $idQuoteSetting)
    {
        $this->idQuoteSetting = $idQuoteSetting;
    }

    public function getIdQuoteSetting(): int
    {
        return $this->idQuoteSetting;
    }
}
