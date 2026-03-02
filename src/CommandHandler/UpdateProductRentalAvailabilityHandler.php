<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\UpdateProductRentalAvailabilityCommand;
use Jca\JcaLocationdevis\Repository\ProductRentalAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateProductRentalAvailabilityHandler
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, ProductRentalAvailabilityRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function handle(UpdateProductRentalAvailabilityCommand $command): void
    {
        $productRental = $this->repository->find($command->getIdProductRentalAvailability());

        if (!$productRental) {
            throw new \InvalidArgumentException('Product rental availability not found');
        }

        if ($command->getProductReference() !== null) {
            $productRental->setProductReference($command->getProductReference());
        }

        if ($command->getProductName() !== null) {
            $productRental->setProductName($command->getProductName());
        }

        if ($command->getProductPrice() !== null) {
            $productRental->setProductPrice($command->getProductPrice());
        }

        if ($command->getRentalEnabled() !== null) {
            $productRental->setRentalEnabled($command->getRentalEnabled());
        }

        if ($command->getDuration36Enabled() !== null) {
            $productRental->setDuration36Enabled($command->getDuration36Enabled());
        }

        if ($command->getDuration60Enabled() !== null) {
            $productRental->setDuration60Enabled($command->getDuration60Enabled());
        }

        $productRental->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
