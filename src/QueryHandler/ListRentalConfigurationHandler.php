<?php

namespace Jca\JcaLocationdevis\QueryHandler;

use Jca\JcaLocationdevis\Query\ListRentalConfigurationQuery;
use Jca\JcaLocationdevis\Repository\RentalConfigurationRepository;

class ListRentalConfigurationHandler
{
    private RentalConfigurationRepository $rentalConfigurationRepository;

    public function __construct(RentalConfigurationRepository $rentalConfigurationRepository)
    {
        $this->rentalConfigurationRepository = $rentalConfigurationRepository;
    }

    public function handle(ListRentalConfigurationQuery $query): array
    {
        $settings = $this->rentalConfigurationRepository->findAll();

        if (empty($settings)) {
            throw new \Exception('Aucun paramètre trouvé.');
        }

        $result = [];
        foreach ($settings as $setting) {
            $result[] = [
                'id' => $setting->getId(),
                'priceMin' => $setting->getPriceMin(),
                'priceMax' => $setting->getPriceMax(),
                'duration36Months' => $setting->getDuration36Months(),
                'duration60Months' => $setting->getDuration60Months(),
                'sortOrder' => $setting->getSortOrder(),
                'dateAdd' => $setting->getDateAdd()->format('Y-m-d H:i:s'),
                'dateUpd' => $setting->getDateUpd()->format('Y-m-d H:i:s'),
            ];
        }

        return $result;
    }
}
