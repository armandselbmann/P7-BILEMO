<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Global Repository related to PaginationService
 */
class GlobalRepository
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Find all with pagination
     *
     * @param int $page
     * @param int $limit
     * @param string $entity
     * @return array
     */
    public function findAllWithPagination(int $page, int $limit, string $entity): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('table');
        $qb->from("$entity",'table');
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    /**
     * Return total pages
     *
     * @param int $limit
     * @param string $entity
     * @return float|int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function totalPage(int $limit, string $entity): float|int
    {

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(table.id)');
        $qb->from("$entity",'table');
        $result = $qb->getQuery()->getSingleScalarResult();
        return $result / $limit;
    }

    /**
     * Find all by a Customer with pagination
     *
     * @param int $page
     * @param int $limit
     * @param string $entity
     * @param int $idCustomer
     * @return array
     */
    public function findAllByCustomerWithPagination(int $page, int $limit, string $entity, int $idCustomer): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('table');
        $qb->from("$entity",'table')
            ->where("table.customers = $idCustomer");
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    /**
     * Return total pages with CustomerId condition
     *
     * @param int $limit
     * @param string $entity
     * @param int $idCustomer
     * @return float|int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function totalPageByCustomer(int $limit, string $entity, int $idCustomer): float|int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(table.id)');
        $qb->from("$entity",'table')
            ->where("table.customers = $idCustomer");
        $result = $qb->getQuery()->getSingleScalarResult();
        return $result / $limit;
    }
}
