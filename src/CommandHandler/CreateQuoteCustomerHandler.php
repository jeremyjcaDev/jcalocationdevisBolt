<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\CreateQuoteCustomerCommand;
use Jca\JcaLocationdevis\Entity\QuoteCustomer;
use Doctrine\ORM\EntityManagerInterface;

class CreateQuoteCustomerHandler
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handle(CreateQuoteCustomerCommand $command): void
    {
        $customer = new QuoteCustomer();
        $customer->setEmail($command->getEmail());
        $customer->setName($command->getName());
        $customer->setPhone($command->getPhone());
        $customer->setIdQuote($command->getIdQuote());
        $customer->setIdCustomerPrestashop($command->getIdCustomerPrestashop());
        $customer->setDateAdd(new \DateTimeImmutable());
        $customer->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->persist($customer);
        $this->entityManager->flush();
    }
}
