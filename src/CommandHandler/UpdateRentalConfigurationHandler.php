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

        if ($command->getPriceMin() !== null) {
            $rentalConfig->setPriceMin($command->getPriceMin());
        }

        if ($command->getPriceMax() !== null) {
            $rentalConfig->setPriceMax($command->getPriceMax());
        }

        if ($command->getDuration36Months() !== null) {
            $rentalConfig->setDuration36Months($command->getDuration36Months());
        }

        if ($command->getDuration60Months() !== null) {
            $rentalConfig->setDuration60Months($command->getDuration60Months());
        }

        if ($command->getSortOrder() !== null) {
            $rentalConfig->setSortOrder($command->getSortOrder());
        }

        $rentalConfig->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
