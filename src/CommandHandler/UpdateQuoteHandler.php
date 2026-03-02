<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\UpdateQuoteCommand;
use Jca\JcaLocationdevis\Repository\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateQuoteHandler
{
    private $entityManager;
    private $quoteRepository;

    public function __construct(EntityManagerInterface $entityManager, QuoteRepository $quoteRepository)
    {
        $this->entityManager = $entityManager;
        $this->quoteRepository = $quoteRepository;
    }

    public function handle(UpdateQuoteCommand $command): void
    {
        $quote = $this->quoteRepository->find($command->getQuoteId());

        if (!$quote) {
            throw new \InvalidArgumentException('Quote not found');
        }

        if ($command->getCustomerName() !== null) {
            $quote->setCustomerName($command->getCustomerName());
        }

        if ($command->getCustomerEmail() !== null) {
            $quote->setCustomerEmail($command->getCustomerEmail());
        }

        if ($command->getCustomerPhone() !== null) {
            $quote->setCustomerPhone($command->getCustomerPhone());
        }

        $quote->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
