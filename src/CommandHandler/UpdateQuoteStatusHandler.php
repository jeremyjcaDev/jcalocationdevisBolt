<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\UpdateQuoteStatusCommand;
use Jca\JcaLocationdevis\Repository\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateQuoteStatusHandler
{
    private $entityManager;
    private $quoteRepository;

    public function __construct(EntityManagerInterface $entityManager, QuoteRepository $quoteRepository)
    {
        $this->entityManager = $entityManager;
        $this->quoteRepository = $quoteRepository;
    }

    public function handle(UpdateQuoteStatusCommand $command): void
    {
        $quote = $this->quoteRepository->find($command->getQuoteId());

        if (!$quote) {
            throw new \InvalidArgumentException('Quote not found');
        }

        $quote->setStatus($command->getNewStatus());
        $quote->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
