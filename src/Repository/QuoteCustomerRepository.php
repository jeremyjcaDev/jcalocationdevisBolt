<?php

namespace Jca\JcaLocationdevis\Repository;

use Jca\JcaLocationdevis\Entity\QuoteCustomer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;

class QuoteCustomerRepository extends EntityRepository
{

    public function findByEmail(string $email): ?QuoteCustomer
    {
        return $this->createQueryBuilder('qc')
            ->andWhere('qc.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function searchByEmail(string $email): array
    {
        return $this->createQueryBuilder('qc')
            ->andWhere('qc.email LIKE :email')
            ->setParameter('email', '%' . $email . '%')
            ->orderBy('qc.dateAdd', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    public function save(QuoteCustomer $quoteCustomer): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($quoteCustomer);
        $entityManager->flush();
    }
}
