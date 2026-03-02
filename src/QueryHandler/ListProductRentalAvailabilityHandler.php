<?php

namespace Jca\JcaLocationdevis\QueryHandler;

use Jca\JcaLocationdevis\Query\ListProductRentalAvailabilityQuery;
use Jca\JcaLocationdevis\Repository\ProductRentalAvailabilityRepository;

class ListProductRentalAvailabilityHandler
{
    private $repository;

    public function __construct(ProductRentalAvailabilityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(ListProductRentalAvailabilityQuery $query)
    {
        $entities = $this->repository->findAll();

        return array_map(function ($entity) {
            return [
                'id' => $entity->getId(),
                'product_id' => $entity->getIdProduct(),
                'product_reference' => $entity->getProductReference(),
                'product_name' => $entity->getProductName(),
                'product_price' => (float)$entity->getProductPrice(),
                'rental_enabled' => $entity->getRentalEnabled(),
                'duration_36_enabled' => $entity->getDuration36Enabled(),
                'duration_60_enabled' => $entity->getDuration60Enabled(),
            ];
        }, $entities);
    }
}
