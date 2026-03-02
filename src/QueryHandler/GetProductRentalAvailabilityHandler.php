<?php

namespace Jca\JcaLocationdevis\QueryHandler;

use Jca\JcaLocationdevis\Query\GetProductRentalAvailabilityQuery;
use Jca\JcaLocationdevis\Repository\ProductRentalAvailabilityRepository;

class GetProductRentalAvailabilityHandler
{
    private $repository;

    public function __construct(ProductRentalAvailabilityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(GetProductRentalAvailabilityQuery $query)
    {
        $entity = $this->repository->findOneByProductId($query->getProductId());

        if (!$entity) {
            throw new \RuntimeException('No product rental availability found for the given product ID.');
        }

        return [
            'id' => $entity->getId(),
            'product_id' => $entity->getIdProduct(),
            'product_reference' => $entity->getProductReference(),
            'product_name' => $entity->getProductName(),
            'product_price' => (float) $entity->getProductPrice(),
            'rental_enabled' => $entity->getRentalEnabled(),
            'duration_36_enabled' => $entity->getDuration36Enabled(),
            'duration_60_enabled' => $entity->getDuration60Enabled(),
        ];
    }
}
