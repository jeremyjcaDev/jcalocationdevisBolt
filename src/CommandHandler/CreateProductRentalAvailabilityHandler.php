<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\CreateProductRentalAvailabilityCommand;
use Jca\JcaLocationdevis\Entity\ProductRentalAvailability;
use Doctrine\ORM\EntityManagerInterface;

class CreateProductRentalAvailabilityHandler
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handle(CreateProductRentalAvailabilityCommand $command): void
    {
        $productRental = new ProductRentalAvailability();
        $productRental->setIdProduct($command->getIdProduct());
        $productRental->setProductReference($command->getProductReference());
        $productRental->setProductName($command->getProductName());
        $productRental->setProductPrice($command->getProductPrice());
        $productRental->setRentalEnabled(true);
        $productRental->setDuration36Enabled(false);
        $productRental->setDuration60Enabled(false);
        $productRental->setDateAdd(new \DateTimeImmutable());
        $productRental->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->persist($productRental);
        $this->entityManager->flush();
    }
}
