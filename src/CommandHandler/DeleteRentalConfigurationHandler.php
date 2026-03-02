<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\DeleteRentalConfigurationCommand;
use Jca\JcaLocationdevis\Repository\RentalConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeleteRentalConfigurationHandler
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, RentalConfigurationRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function handle(DeleteRentalConfigurationCommand $command): void
    {
        $rentalConfig = $this->repository->find($command->getIdRentalConfiguration());

        if (!$rentalConfig) {
            throw new \InvalidArgumentException('Rental configuration not found');
        }

        $this->entityManager->remove($rentalConfig);
        $this->entityManager->flush();
    }
}
