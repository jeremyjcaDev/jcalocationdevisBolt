<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\UpdateRentalConfigurationCommand;
use Jca\JcaLocationdevis\Repository\RentalConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateRentalConfigurationHandler
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, RentalConfigurationRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function handle(UpdateRentalConfigurationCommand $command): void
    {
        $rentalConfig = $this->repository->find($command->getIdRentalConfiguration());

        if (!$rentalConfig) {
            throw new \InvalidArgumentException('Rental configuration not found');
        }

        $priceMin = $command->getPriceMin();
        if ($priceMin !== null) {
            $rentalConfig->setPriceMin((float)$priceMin);
        }

        $priceMax = $command->getPriceMax();
        if ($priceMax !== null) {
            $rentalConfig->setPriceMax((float)$priceMax);
        }

        $duration36 = $command->getDuration36Months();
        if ($duration36 !== null) {
            $rentalConfig->setDuration36Months((float)$duration36);
        }

        $duration60 = $command->getDuration60Months();
        if ($duration60 !== null) {
            $rentalConfig->setDuration60Months((float)$duration60);
        }

        $sortOrder = $command->getSortOrder();
        if ($sortOrder !== null) {
            $rentalConfig->setSortOrder((int)$sortOrder);
        }

        $rentalConfig->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
