<?php

namespace Jca\JcaLocationdevis\QueryHandler;

use Jca\JcaLocationdevis\Query\GetQuoteWithItemsQuery;
use Jca\JcaLocationdevis\Repository\QuoteRepository;
use Jca\JcaLocationdevis\Repository\QuoteItemRepository;

class GetQuoteWithItemsHandler
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

    public function handle(GetQuoteWithItemsQuery $query): array
    {
        $quote = $this->quoteRepository->find($query->getQuoteId());

        if (!$quote) {
            throw new \Exception('Devis non trouvé');
        }

        $items = $this->quoteItemRepository->findByQuoteId($quote->getId());

        $itemsData = [];
        foreach ($items as $item) {
            $itemsData[] = [
                'id_quote_item' => $item->getId(),
                'product_id' => $item->getIdProduct(),
                'product_reference' => $item->getProductReference(),
                'product_name' => $item->getProductName(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
                'original_price' => $item->getOriginalPrice(),
                'discount_percentage' => $item->getDiscountPercentage(),
                'is_rental' => $item->getIsRental(),
                'duration_months' => $item->getDurationMonths(),
                'rate_percentage' => $item->getRatePercentage(),
                'rental_config_id' => $item->getIdRentalConfiguration(),
                'date_add' => $item->getDateAdd()->format('Y-m-d H:i:s')
            ];
        }

        return [
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
            'items' => $itemsData
        ];
    }
}
