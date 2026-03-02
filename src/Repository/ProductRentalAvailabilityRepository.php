<?php

namespace Jca\JcaLocationdevis\Repository;

use Jca\JcaLocationdevis\Entity\ProductRentalAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;

class ProductRentalAvailabilityRepository extends EntityRepository
{

    public function findAll(): array
    {
        return $this->createQueryBuilder('pra')
            ->orderBy('pra.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableRentals(): array
    {
        return $this->createQueryBuilder('pra')
            ->andWhere('pra.rentalEnabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();
    }
    public function findOneByProductId(int $productId): ?ProductRentalAvailability
    {
        return $this->createQueryBuilder('pra')
            ->andWhere('pra.idProduct = :productId')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function save(ProductRentalAvailability $productRentalAvailability): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($productRentalAvailability);
        $entityManager->flush();
    }
}
