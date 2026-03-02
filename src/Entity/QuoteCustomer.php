<?php

namespace Jca\JcaLocationdevis\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jca\JcaLocationdevis\Repository\QuoteCustomerRepository;

/**
 * @ORM\Entity(repositoryClass=QuoteCustomerRepository::class)
 * @ORM\Table(name="ps_jca_quote_customers")
 */
class QuoteCustomer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id_customer", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="id_quote", type="integer", nullable=true)
     */
    private $idQuote;

    /**
     * @ORM\Column(name="id_customer_prestashop", type="integer", nullable=true)
     */
    private $idCustomerPrestashop;

    /**
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(name="phone", type="string", length=50, nullable=true)
     */
    private $phone;

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

    public function getIdQuote(): ?int
    {
        return $this->idQuote;
    }

    public function setIdQuote(?int $idQuote): self
    {
        $this->idQuote = $idQuote;
        return $this;
    }

    public function getIdCustomerPrestashop(): ?int
    {
        return $this->idCustomerPrestashop;
    }

    public function setIdCustomerPrestashop(?int $idCustomerPrestashop): self
    {
        $this->idCustomerPrestashop = $idCustomerPrestashop;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
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
