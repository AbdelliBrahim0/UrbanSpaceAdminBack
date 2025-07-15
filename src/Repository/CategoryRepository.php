<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findWithPagination(int $page = 1, int $limit = 10, bool $activeOnly = false): Paginator
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.categorieParent', 'parent')
            ->addSelect('parent');

        $query = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query);
    }

    public function findBySearchTerm(string $searchTerm, bool $activeOnly = false): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.categorieParent', 'parent')
            ->addSelect('parent')
            ->where('c.nom LIKE :searchTerm')
            ->orWhere('c.description LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $qb->getQuery()->getResult();
    }

    public function findParentCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.categorieParent IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function findSubCategories(Category $parent): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.categorieParent = :parent')
            ->setParameter('parent', $parent)
            ->andWhere('c.actif = true')
            ->orderBy('c.ordre', 'ASC')
            ->addOrderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}