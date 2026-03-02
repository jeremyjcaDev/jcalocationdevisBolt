<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\CreateRentalConfigurationCommand;
use Jca\JcaLocationdevis\Entity\RentalConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use Jca\JcaLocationdevis\Repository\RentalConfigurationRepository;

class CreateRentalConfigurationHandler
{
    private RentalConfigurationRepository $rentalConfigurationRepository;
    private CommandBusInterface $commandBus;

    public function __construct(RentalConfigurationRepository $rentalConfigurationRepository, CommandBusInterface $commandBus)
    {
        $this->rentalConfigurationRepository = $rentalConfigurationRepository;
        $this->commandBus = $commandBus;
    }
    public function handle(CreateRentalConfigurationCommand $command): void
    {
        $rentalConfig = new RentalConfiguration();
        $rentalConfig->setPriceMin($command->getPriceMin());
        $rentalConfig->setPriceMax($command->getPriceMax());
        $rentalConfig->setDuration36Months($command->getDuration36Months());
        $rentalConfig->setDuration60Months($command->getDuration60Months());
        $rentalConfig->setSortOrder($command->getSortOrder());
        $rentalConfig->setDateAdd(new \DateTimeImmutable());
        $rentalConfig->setDateUpd(new \DateTimeImmutable());

        $this->rentalConfigurationRepository->save($rentalConfig);
    }
}
