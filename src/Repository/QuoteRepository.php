<?php

namespace Jca\JcaLocationdevis\Repository;

use Doctrine\ORM\EntityRepository;
use Jca\JcaLocationdevis\Entity\Quote;

class QuoteRepository extends EntityRepository
{
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.status = :status')
            ->setParameter('status', $status)
            ->orderBy('q.dateAdd', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCustomerEmail(string $email): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.customerEmail = :email')
            ->setParameter('email', $email)
            ->orderBy('q.dateAdd', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findExpiredQuotes(): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.status = :status')
            ->andWhere('q.validUntil < :now')
            ->setParameter('status', 'pending')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    public function save(Quote $quote): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($quote);
        $entityManager->flush();
    }
}
