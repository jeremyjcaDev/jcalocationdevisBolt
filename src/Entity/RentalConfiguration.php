<?php

namespace Jca\JcaLocationdevis\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jca\JcaLocationdevis\Repository\RentalConfigurationRepository;

/**
 * @ORM\Entity(repositoryClass=RentalConfigurationRepository::class)
 * @ORM\Table(name="ps_jca_rental_configurations")
 */
class RentalConfiguration
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id_rental_configuration", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="price_min", type="decimal", precision=10, scale=2)
     */
    private $priceMin;

    /**
     * @ORM\Column(name="price_max", type="decimal", precision=10, scale=2)
     */
    private $priceMax;

    /**
     * @ORM\Column(name="duration_36_months", type="decimal", precision=5, scale=2)
     */
    private $duration36Months;

    /**
     * @ORM\Column(name="duration_60_months", type="decimal", precision=5, scale=2)
     */
    private $duration60Months;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    private $sortOrder;

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

    public function getPriceMin(): float
    {
        return $this->priceMin;
    }

    public function setPriceMin(float $priceMin): self
    {
        $this->priceMin = $priceMin;
        return $this;
    }

    public function getPriceMax(): float
    {
        return $this->priceMax;
    }

    public function setPriceMax(float $priceMax): self
    {
        $this->priceMax = $priceMax;
        return $this;
    }

    public function getDuration36Months(): float
    {
        return $this->duration36Months;
    }

    public function setDuration36Months(float $duration36Months): self
    {
        $this->duration36Months = $duration36Months;
        return $this;
    }

    public function getDuration60Months(): float
    {
        return $this->duration60Months;
    }

    public function setDuration60Months(float $duration60Months): self
    {
        $this->duration60Months = $duration60Months;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
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
