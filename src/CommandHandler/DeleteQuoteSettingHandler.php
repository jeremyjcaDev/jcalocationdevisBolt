<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\DeleteQuoteSettingCommand;
use Jca\JcaLocationdevis\Repository\QuoteSettingRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeleteQuoteSettingHandler
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, QuoteSettingRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function handle(DeleteQuoteSettingCommand $command): void
    {
        $quoteSetting = $this->repository->find($command->getIdQuoteSetting());

        if (!$quoteSetting) {
            throw new \InvalidArgumentException('Quote setting not found');
        }

        $this->entityManager->remove($quoteSetting);
        $this->entityManager->flush();
    }
}
