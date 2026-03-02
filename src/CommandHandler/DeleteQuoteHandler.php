<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\DeleteQuoteCommand;
use Jca\JcaLocationdevis\Repository\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeleteQuoteHandler
{
    private $entityManager;
    private $quoteRepository;

    public function __construct(EntityManagerInterface $entityManager, QuoteRepository $quoteRepository)
    {
        $this->entityManager = $entityManager;
        $this->quoteRepository = $quoteRepository;
    }

    public function handle(DeleteQuoteCommand $command): void
    {
        $quote = $this->quoteRepository->find($command->getQuoteId());

        if (!$quote) {
            throw new \InvalidArgumentException('Quote not found');
        }

        $this->entityManager->remove($quote);
        $this->entityManager->flush();
    }
}
