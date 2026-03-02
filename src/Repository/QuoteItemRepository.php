<?php

namespace Jca\JcaLocationdevis\Repository;

use Jca\JcaLocationdevis\Entity\QuoteItem;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuoteItemRepository extends EntityRepository
{

    public function findByQuoteId(int $quoteId): array
    {
        return $this->createQueryBuilder('qi')
            ->andWhere('qi.idQuote = :quoteId')
            ->setParameter('quoteId', $quoteId)
            ->getQuery()
            ->getResult();
    }

    public function save(QuoteItem $quoteItem): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($quoteItem);
        $entityManager->flush();
    }

    // Add custom methods here
}
