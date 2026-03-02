<?php

namespace Jca\JcaLocationdevis\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Jca\JcaLocationdevis\Entity\QuoteSetting;

class QuoteSettingRepository extends EntityRepository
{

    // Custom query methods...
    public function findLatestSettings(): ?QuoteSetting
    {
        return $this->createQueryBuilder('qs')
            ->orderBy('qs.dateUpd', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(QuoteSetting $quoteSetting): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($quoteSetting);
        $entityManager->flush();
    }
}