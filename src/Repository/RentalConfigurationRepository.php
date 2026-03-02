<?php

namespace Jca\JcaLocationdevis\Repository;

use Jca\JcaLocationdevis\Entity\RentalConfiguration;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;


class RentalConfigurationRepository extends EntityRepository
{

    // Custom query methods...
    public function findByPriceRange(float $min, float $max): array
    {
        return $this->createQueryBuilder('rc')
            ->andWhere('rc.priceMin >= :min')
            ->andWhere('rc.priceMax <= :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->getQuery()
            ->getResult();
    }

    public function save(RentalConfiguration $rentalConfiguration): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($rentalConfiguration);
        $entityManager->flush();
    }

    public function findRangeByPrice(float $price): ?RentalConfiguration
    {
        return $this->createQueryBuilder('rc')
            ->andWhere(':price BETWEEN rc.priceMin AND rc.priceMax')
            ->setParameter('price', $price)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
