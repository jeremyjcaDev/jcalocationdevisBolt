<?php

namespace Jca\JcaLocationdevis\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jca\JcaLocationdevis\Repository\QuoteSettingRepository;

/**
 * @ORM\Entity(repositoryClass=QuoteSettingRepository::class)
 * @ORM\Table(name="ps_jca_quote_settings")
 */
class QuoteSetting
{
     /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id_quote_settings", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="validity_hours", type="integer")
     */
    private $validityHours;

    /**
     * @ORM\Column(name="quote_number_prefix", type="string", length=50)
     */
    private $quoteNumberPrefix;

    /**
     * @ORM\Column(name="quote_number_year_format", type="string", length=10)
     */
    private $quoteNumberYearFormat;

    /**
     * @ORM\Column(name="quote_number_separator", type="string", length=5)
     */
    private $quoteNumberSeparator;

    /**
     * @ORM\Column(name="quote_number_padding", type="integer")
     */
    private $quoteNumberPadding;

    /**
     * @ORM\Column(name="quote_number_counter", type="integer")
     */
    private $quoteNumberCounter;

    /**
     * @ORM\Column(name="quote_number_reset_yearly", type="boolean")
     */
    private $quoteNumberResetYearly;

    /**
     * @ORM\Column(name="quote_number_last_year", type="integer")
     */
    private $quoteNumberLastYear;

    /**
     * @ORM\Column(name="email_notifications_enabled", type="boolean")
     */
    private $emailNotificationsEnabled;

    /**
     * @ORM\Column(name="email_on_quote_created", type="boolean")
     */
    private $emailOnQuoteCreated;

    /**
     * @ORM\Column(name="email_on_quote_validated", type="boolean")
     */
    private $emailOnQuoteValidated;

    /**
     * @ORM\Column(name="email_on_quote_refused", type="boolean")
     */
    private $emailOnQuoteRefused;

    /**
     * @ORM\Column(name="email_on_quote_expiring", type="boolean")
     */
    private $emailOnQuoteExpiring;

    /**
     * @ORM\Column(name="email_expiring_days_before", type="integer")
     */
    private $emailExpiringDaysBefore;

    /**
     * @ORM\Column(name="email_sender_name", type="string", length=255)
     */
    private $emailSenderName;

    /**
     * @ORM\Column(name="email_sender_email", type="string", length=255)
     */
    private $emailSenderEmail;

    /**
     * @ORM\Column(name="email_reply_to", type="string", length=255)
     */
    private $emailReplyTo;

    /**
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $dateAdd;

    /**
     * @ORM\Column(name="date_upd", type="datetime")
     */
    private $dateUpd;

    // Getters and setters...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValidityHours(): int
    {
        return $this->validityHours;
    }

    public function setValidityHours(int $validityHours): self
    {
        $this->validityHours = $validityHours;
        return $this;
    }

    public function getQuoteNumberPrefix(): string
    {
        return $this->quoteNumberPrefix;
    }

    public function setQuoteNumberPrefix(string $quoteNumberPrefix): self
    {
        $this->quoteNumberPrefix = $quoteNumberPrefix;
        return $this;
    }

    public function getQuoteNumberYearFormat(): string
    {
        return $this->quoteNumberYearFormat;
    }

    public function setQuoteNumberYearFormat(string $quoteNumberYearFormat): self
    {
        $this->quoteNumberYearFormat = $quoteNumberYearFormat;
        return $this;
    }

    public function getQuoteNumberSeparator(): string
    {
        return $this->quoteNumberSeparator;
    }

    public function setQuoteNumberSeparator(string $quoteNumberSeparator): self
    {
        $this->quoteNumberSeparator = $quoteNumberSeparator;
        return $this;
    }

    public function getQuoteNumberPadding(): int
    {
        return $this->quoteNumberPadding;
    }

    public function setQuoteNumberPadding(int $quoteNumberPadding): self
    {
        $this->quoteNumberPadding = $quoteNumberPadding;
        return $this;
    }

    public function getQuoteNumberCounter(): int
    {
        return $this->quoteNumberCounter;
    }

    public function setQuoteNumberCounter(int $quoteNumberCounter): self
    {
        $this->quoteNumberCounter = $quoteNumberCounter;
        return $this;
    }

    public function getQuoteNumberResetYearly(): bool
    {
        return $this->quoteNumberResetYearly;
    }

    public function setQuoteNumberResetYearly(bool $quoteNumberResetYearly): self
    {
        $this->quoteNumberResetYearly = $quoteNumberResetYearly;
        return $this;
    }

    public function getQuoteNumberLastYear(): int
    {
        return $this->quoteNumberLastYear;
    }

    public function setQuoteNumberLastYear(int $quoteNumberLastYear): self
    {
        $this->quoteNumberLastYear = $quoteNumberLastYear;
        return $this;
    }

    public function getEmailNotificationsEnabled(): bool
    {
        return $this->emailNotificationsEnabled;
    }

    public function setEmailNotificationsEnabled(bool $emailNotificationsEnabled): self
    {
        $this->emailNotificationsEnabled = $emailNotificationsEnabled;
        return $this;
    }

    public function getEmailOnQuoteCreated(): bool
    {
        return $this->emailOnQuoteCreated;
    }

    public function setEmailOnQuoteCreated(bool $emailOnQuoteCreated): self
    {
        $this->emailOnQuoteCreated = $emailOnQuoteCreated;
        return $this;
    }

    public function getEmailOnQuoteValidated(): bool
    {
        return $this->emailOnQuoteValidated;
    }

    public function setEmailOnQuoteValidated(bool $emailOnQuoteValidated): self
    {
        $this->emailOnQuoteValidated = $emailOnQuoteValidated;
        return $this;
    }

    public function getEmailOnQuoteRefused(): bool
    {
        return $this->emailOnQuoteRefused;
    }

    public function setEmailOnQuoteRefused(bool $emailOnQuoteRefused): self
    {
        $this->emailOnQuoteRefused = $emailOnQuoteRefused;
        return $this;
    }

    public function getEmailOnQuoteExpiring(): bool
    {
        return $this->emailOnQuoteExpiring;
    }

    public function setEmailOnQuoteExpiring(bool $emailOnQuoteExpiring): self
    {
        $this->emailOnQuoteExpiring = $emailOnQuoteExpiring;
        return $this;
    }

    public function getEmailExpiringDaysBefore(): int
    {
        return $this->emailExpiringDaysBefore;
    }

    public function setEmailExpiringDaysBefore(int $emailExpiringDaysBefore): self
    {
        $this->emailExpiringDaysBefore = $emailExpiringDaysBefore;
        return $this;
    }

    public function getEmailSenderName(): string
    {
        return $this->emailSenderName;
    }

    public function setEmailSenderName(string $emailSenderName): self
    {
        $this->emailSenderName = $emailSenderName;
        return $this;
    }

    public function getEmailSenderEmail(): string
    {
        return $this->emailSenderEmail;
    }

    public function setEmailSenderEmail(string $emailSenderEmail): self
    {
        $this->emailSenderEmail = $emailSenderEmail;
        return $this;
    }

    public function getEmailReplyTo(): string
    {
        return $this->emailReplyTo;
    }

    public function setEmailReplyTo(string $emailReplyTo): self
    {
        $this->emailReplyTo = $emailReplyTo;
        return $this;
    }

    public function getDateAdd(): \DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;
        return $this;
    }

    public function getDateUpd(): \DateTimeInterface
    {
        return $this->dateUpd;
    }

    public function setDateUpd(\DateTimeInterface $dateUpd): self
    {
        $this->dateUpd = $dateUpd;
        return $this;
    }
}