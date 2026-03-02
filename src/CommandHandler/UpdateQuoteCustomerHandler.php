<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\UpdateQuoteCustomerCommand;
use Jca\JcaLocationdevis\Repository\QuoteCustomerRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateQuoteCustomerHandler
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, QuoteCustomerRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function handle(UpdateQuoteCustomerCommand $command): void
    {
        $customer = $this->repository->find($command->getIdCustomer());

        if (!$customer) {
            throw new \InvalidArgumentException('Customer not found');
        }

        if ($command->getEmail() !== null) {
            $customer->setEmail($command->getEmail());
        }

        if ($command->getName() !== null) {
            $customer->setName($command->getName());
        }

        if ($command->getPhone() !== null) {
            $customer->setPhone($command->getPhone());
        }

        if ($command->getIdQuote() !== null) {
            $customer->setIdQuote($command->getIdQuote());
        }

        if ($command->getIdCustomerPrestashop() !== null) {
            $customer->setIdCustomerPrestashop($command->getIdCustomerPrestashop());
        }

        $customer->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
