<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findWithPagination(int $page = 1, int $limit = 10): Paginator
    {
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.dateInscription', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query);
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.nom LIKE :searchTerm')
            ->orWhere('u.email LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('u.dateInscription', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countTotal(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}