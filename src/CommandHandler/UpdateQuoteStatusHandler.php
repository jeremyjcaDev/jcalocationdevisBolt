<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\UpdateQuoteStatusCommand;
use Jca\JcaLocationdevis\Repository\QuoteRepository;
use Jca\JcaLocationdevis\Service\QuoteEmailService;
use Doctrine\ORM\EntityManagerInterface;

class UpdateQuoteStatusHandler
{
    private $entityManager;
    private $quoteRepository;
    private $emailService;

    public function __construct(
        EntityManagerInterface $entityManager,
        QuoteRepository $quoteRepository,
        QuoteEmailService $emailService
    )
    {
        $this->entityManager = $entityManager;
        $this->quoteRepository = $quoteRepository;
        $this->emailService = $emailService;
    }

    public function handle(UpdateQuoteStatusCommand $command): void
    {
        $quote = $this->quoteRepository->find($command->getQuoteId());

        if (!$quote) {
            throw new \InvalidArgumentException('Quote not found');
        }

        $oldStatus = $quote->getStatus();
        $newStatus = $command->getNewStatus();

        $quote->setStatus($newStatus);
        $quote->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->flush();

        if (in_array($newStatus, ['validated', 'refused']) && $oldStatus !== $newStatus) {
            $db = \Db::getInstance();
            $quoteData = $db->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'jca_quotes` WHERE id_quote = ' . (int)$command->getQuoteId());
            $items = $db->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_items` WHERE id_quote = ' . (int)$command->getQuoteId());

            $this->emailService->sendQuoteStatusEmail($quoteData, $items, $newStatus);
        }
    }
}
