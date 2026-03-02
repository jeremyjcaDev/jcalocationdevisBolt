<?php

namespace Jca\JcaLocationdevis\Command;

class UpdateQuoteSettingCommand
{
    private $idQuoteSetting;
    private $validityHours;
    private $quoteNumberPrefix;
    private $quoteNumberYearFormat;
    private $quoteNumberSeparator;
    private $quoteNumberPadding;
    private $quoteNumberCounter;
    private $quoteNumberResetYearly;
    private $emailNotificationsEnabled;
    private $emailOnQuoteCreated;
    private $emailOnQuoteValidated;
    private $emailOnQuoteRefused;
    private $emailOnQuoteExpiring;
    private $emailExpiringDaysBefore;
    private $emailSenderName;
    private $emailSenderEmail;
    private $emailReplyTo;

    public function __construct(
        int $idQuoteSetting,
        ?int $validityHours = null,
        ?string $quoteNumberPrefix = null,
        ?string $quoteNumberYearFormat = null,
        ?string $quoteNumberSeparator = null,
        ?int $quoteNumberPadding = null,
        ?int $quoteNumberCounter = null,
        ?bool $quoteNumberResetYearly = null,
        ?bool $emailNotificationsEnabled = null,
        ?bool $emailOnQuoteCreated = null,
        ?bool $emailOnQuoteValidated = null,
        ?bool $emailOnQuoteRefused = null,
        ?bool $emailOnQuoteExpiring = null,
        ?int $emailExpiringDaysBefore = null,
        ?string $emailSenderName = null,
        ?string $emailSenderEmail = null,
        ?string $emailReplyTo = null
    ) {
        $this->idQuoteSetting = $idQuoteSetting;
        $this->validityHours = $validityHours;
        $this->quoteNumberPrefix = $quoteNumberPrefix;
        $this->quoteNumberYearFormat = $quoteNumberYearFormat;
        $this->quoteNumberSeparator = $quoteNumberSeparator;
        $this->quoteNumberPadding = $quoteNumberPadding;
        $this->quoteNumberCounter = $quoteNumberCounter;
        $this->quoteNumberResetYearly = $quoteNumberResetYearly;
        $this->emailNotificationsEnabled = $emailNotificationsEnabled;
        $this->emailOnQuoteCreated = $emailOnQuoteCreated;
        $this->emailOnQuoteValidated = $emailOnQuoteValidated;
        $this->emailOnQuoteRefused = $emailOnQuoteRefused;
        $this->emailOnQuoteExpiring = $emailOnQuoteExpiring;
        $this->emailExpiringDaysBefore = $emailExpiringDaysBefore;
        $this->emailSenderName = $emailSenderName;
        $this->emailSenderEmail = $emailSenderEmail;
        $this->emailReplyTo = $emailReplyTo;
    }

    public function getIdQuoteSetting(): int
    {
        return $this->idQuoteSetting;
    }

    public function getValidityHours(): ?int
    {
        return $this->validityHours;
    }

    public function getQuoteNumberPrefix(): ?string
    {
        return $this->quoteNumberPrefix;
    }

    public function getQuoteNumberYearFormat(): ?string
    {
        return $this->quoteNumberYearFormat;
    }

    public function getQuoteNumberSeparator(): ?string
    {
        return $this->quoteNumberSeparator;
    }

    public function getQuoteNumberPadding(): ?int
    {
        return $this->quoteNumberPadding;
    }

    public function getQuoteNumberCounter(): ?int
    {
        return $this->quoteNumberCounter;
    }

    public function getQuoteNumberResetYearly(): ?bool
    {
        return $this->quoteNumberResetYearly;
    }

    public function getEmailNotificationsEnabled(): ?bool
    {
        return $this->emailNotificationsEnabled;
    }

    public function getEmailOnQuoteCreated(): ?bool
    {
        return $this->emailOnQuoteCreated;
    }

    public function getEmailOnQuoteValidated(): ?bool
    {
        return $this->emailOnQuoteValidated;
    }

    public function getEmailOnQuoteRefused(): ?bool
    {
        return $this->emailOnQuoteRefused;
    }

    public function getEmailOnQuoteExpiring(): ?bool
    {
        return $this->emailOnQuoteExpiring;
    }

    public function getEmailExpiringDaysBefore(): ?int
    {
        return $this->emailExpiringDaysBefore;
    }

    public function getEmailSenderName(): ?string
    {
        return $this->emailSenderName;
    }

    public function getEmailSenderEmail(): ?string
    {
        return $this->emailSenderEmail;
    }

    public function getEmailReplyTo(): ?string
    {
        return $this->emailReplyTo;
    }
}
