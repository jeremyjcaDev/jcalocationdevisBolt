<?php

namespace Jca\JcaLocationdevis\QueryHandler;

use Jca\JcaLocationdevis\Query\ListQuotesQuery;
use Jca\JcaLocationdevis\Repository\QuoteRepository;
use Jca\JcaLocationdevis\Repository\QuoteItemRepository;

class ListQuotesHandler
{
    private $quoteRepository;
    private $quoteItemRepository;

    public function __construct(
        QuoteRepository $quoteRepository,
        QuoteItemRepository $quoteItemRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteItemRepository = $quoteItemRepository;
    }

    public function handle(ListQuotesQuery $query): array
    {
        $qb = $this->quoteRepository->createQueryBuilder('q')
            ->orderBy('q.dateAdd', 'DESC');

        if ($query->getStatus()) {
            $qb->andWhere('q.status = :status')
                ->setParameter('status', $query->getStatus());
        }

        $quotes = $qb->getQuery()->getResult();

        $result = [];
        foreach ($quotes as $quote) {
            $items = $this->quoteItemRepository->findByQuoteId($quote->getId());

            $totalAmount = 0;
            $monthlyPayment = 0;
            $durationMonths = null;

            foreach ($items as $item) {
                if ($item->getIsRental() && $item->getDurationMonths() && $item->getRatePercentage()) {
                    $price = $item->getPrice();
                    $monthlyRate = ($item->getRatePercentage() / 100) / 12;
                    $months = $item->getDurationMonths();
                    $payment = $price * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);
                    $monthlyPayment += $payment;
                    $durationMonths = $item->getDurationMonths();
                } else {
                    $totalAmount += $item->getPrice();
                }
            }

            $result[] = [
                'id_quote' => $quote->getId(),
                'quote_number' => $quote->getQuoteNumber(),
                'quote_type' => $quote->getQuoteType(),
                'customer_name' => $quote->getCustomerName(),
                'customer_email' => $quote->getCustomerEmail(),
                'customer_phone' => $quote->getCustomerPhone(),
                'status' => $quote->getStatus(),
                'valid_until' => $quote->getValidUntil() ? $quote->getValidUntil()->format('Y-m-d H:i:s') : null,
                'date_add' => $quote->getDateAdd()->format('Y-m-d H:i:s'),
                'date_upd' => $quote->getDateUpd()->format('Y-m-d H:i:s'),
                'total_amount' => $totalAmount,
                'monthly_payment' => $monthlyPayment > 0 ? $monthlyPayment : null,
                'duration_months' => $durationMonths,
                'is_registered' => !empty($quote->getCustomerEmail())
            ];
        }

        return $result;
    }
}
