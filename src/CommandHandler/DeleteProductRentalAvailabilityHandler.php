<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\DeleteProductRentalAvailabilityCommand;
use Jca\JcaLocationdevis\Repository\ProductRentalAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeleteProductRentalAvailabilityHandler
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, ProductRentalAvailabilityRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function handle(DeleteProductRentalAvailabilityCommand $command): void
    {
        $productRental = $this->repository->find($command->getIdProductRentalAvailability());

        if (!$productRental) {
            throw new \InvalidArgumentException('Product rental availability not found');
        }

        $this->entityManager->remove($productRental);
        $this->entityManager->flush();
    }
}
