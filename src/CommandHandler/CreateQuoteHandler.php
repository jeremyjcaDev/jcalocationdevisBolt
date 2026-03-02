<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\CreateQuoteCommand;
use Jca\JcaLocationdevis\Entity\Quote;
use Jca\JcaLocationdevis\Entity\QuoteItem;
use Jca\JcaLocationdevis\Repository\QuoteRepository;
use Jca\JcaLocationdevis\Repository\QuoteSettingRepository;
use Doctrine\ORM\EntityManagerInterface;

class CreateQuoteHandler
{
    private $entityManager;
    private $quoteRepository;
    private $quoteSettingRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        QuoteRepository $quoteRepository,
        QuoteSettingRepository $quoteSettingRepository
    ) {
        $this->entityManager = $entityManager;
        $this->quoteRepository = $quoteRepository;
        $this->quoteSettingRepository = $quoteSettingRepository;
    }

    public function handle(CreateQuoteCommand $command): int
    {
        $quoteNumber = $this->generateQuoteNumber();

        $quote = new Quote();
        $quote->setQuoteNumber($quoteNumber);
        $quote->setQuoteType($command->getQuoteType());
        $quote->setCustomerName($command->getCustomerName());
        $quote->setCustomerEmail($command->getCustomerEmail());
        $quote->setCustomerPhone($command->getCustomerPhone());
        $quote->setStatus($command->getStatus());

        if ($command->getValidUntil()) {
            $validUntil = new \DateTime($command->getValidUntil());
            $quote->setValidUntil($validUntil);
        }

        $quote->setDateAdd(new \DateTimeImmutable());
        $quote->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->persist($quote);
        $this->entityManager->flush();

        $items = $command->getItems();
        if (!empty($items)) {
            foreach ($items as $itemData) {
                $quoteItem = new QuoteItem();
                $quoteItem->setIdQuote($quote->getId());
                $quoteItem->setIdProduct($itemData['product_id'] ?? $itemData['idProduct'] ?? 0);
                $quoteItem->setProductReference($itemData['product_reference'] ?? $itemData['productReference'] ?? '');
                $quoteItem->setProductName($itemData['product_name'] ?? $itemData['productName'] ?? '');
                $quoteItem->setQuantity($itemData['quantity'] ?? 1);
                $quoteItem->setPrice($itemData['price'] ?? 0);
                $quoteItem->setOriginalPrice($itemData['original_price'] ?? $itemData['originalPrice'] ?? null);
                $quoteItem->setDiscountPercentage($itemData['discount_percentage'] ?? $itemData['discountPercentage'] ?? null);
                $quoteItem->setIsRental($itemData['is_rental'] ?? $itemData['isRental'] ?? false);
                $quoteItem->setDurationMonths($itemData['duration_months'] ?? $itemData['durationMonths'] ?? null);
                $quoteItem->setRatePercentage($itemData['rate_percentage'] ?? $itemData['ratePercentage'] ?? null);
                $quoteItem->setIdRentalConfiguration($itemData['rental_config_id'] ?? $itemData['rentalConfigId'] ?? null);
                $quoteItem->setDateAdd(new \DateTimeImmutable());

                $this->entityManager->persist($quoteItem);
            }

            $this->entityManager->flush();
        }

        return $quote->getId();
    }

    private function generateQuoteNumber(): string
    {
        $settings = $this->quoteSettingRepository->findLatestSettings();

        if (!$settings) {
            return 'DEVIS-' . date('Y') . '-001';
        }

        $year = (int)date('Y');
        $yearFormat = $settings->getQuoteNumberYearFormat() === 'YY'
            ? substr((string)$year, -2)
            : (string)$year;

        $counter = $settings->getQuoteNumberCounter() + 1;
        if ($settings->getQuoteNumberResetYearly() && $settings->getQuoteNumberLastYear() !== $year) {
            $counter = 1;
        }

        $settings->setQuoteNumberCounter($counter);
        $settings->setQuoteNumberLastYear($year);
        $settings->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->persist($settings);

        $paddedCounter = str_pad((string)$counter, $settings->getQuoteNumberPadding(), '0', STR_PAD_LEFT);
        return $settings->getQuoteNumberPrefix()
            . $settings->getQuoteNumberSeparator()
            . $yearFormat
            . $settings->getQuoteNumberSeparator()
            . $paddedCounter;
    }
}
