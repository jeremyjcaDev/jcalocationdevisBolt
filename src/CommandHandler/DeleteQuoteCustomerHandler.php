<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\DeleteQuoteCustomerCommand;
use Jca\JcaLocationdevis\Repository\QuoteCustomerRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeleteQuoteCustomerHandler
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, QuoteCustomerRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function handle(DeleteQuoteCustomerCommand $command): void
    {
        $customer = $this->repository->find($command->getIdCustomer());

        if (!$customer) {
            throw new \InvalidArgumentException('Customer not found');
        }

        $this->entityManager->remove($customer);
        $this->entityManager->flush();
    }
}
