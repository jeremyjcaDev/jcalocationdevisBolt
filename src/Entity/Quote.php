<?php

namespace Jca\JcaLocationdevis\Entity;

use Jca\JcaLocationdevis\Repository\QuoteRepository;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuoteRepository::class)
 * @ORM\Table(name="ps_jca_quotes")
 */
class Quote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id_quote", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="quote_number", type="string", length=100)
     */
    private $quoteNumber;

    /**
     * @ORM\Column(name="quote_type", type="string", columnDefinition="ENUM('standard','rental_only')")
     */
    private $quoteType;

    /**
     * @ORM\Column(name="customer_name", type="string", length=255, nullable=true)
     */
    private $customerName;

    /**
     * @ORM\Column(name="customer_email", type="string", length=255, nullable=true)
     */
    private $customerEmail;

    /**
     * @ORM\Column(name="customer_phone", type="string", length=50, nullable=true)
     */
    private $customerPhone;

    /**
     * @ORM\Column(name="status", type="string", columnDefinition="ENUM('draft','pending','validated','expired','refused')")
     */
    private $status;

    /**
     * @ORM\Column(name="valid_until", type="datetime", nullable=true)
     */
    private $validUntil;

    /**
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $dateAdd;

    /**
     * @ORM\Column(name="date_upd", type="datetime")
     */
    private $dateUpd;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuoteNumber(): string
    {
        return $this->quoteNumber;
    }

    public function setQuoteNumber(string $quoteNumber): self
    {
        $this->quoteNumber = $quoteNumber;
        return $this;
    }

    public function getQuoteType(): string
    {
        return $this->quoteType;
    }

    public function setQuoteType(string $quoteType): self
    {
        $this->quoteType = $quoteType;
        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(?string $customerName): self
    {
        $this->customerName = $customerName;
        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(?string $customerEmail): self
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(?string $customerPhone): self
    {
        $this->customerPhone = $customerPhone;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTimeInterface $validUntil): self
    {
        $this->validUntil = $validUntil;
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
