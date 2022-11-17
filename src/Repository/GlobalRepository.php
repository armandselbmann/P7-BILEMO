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
     * @param $page
     * @param $limit
     * @param $entity
     * @return float|int|mixed|string
     */
    public function findAllWithPagination($page, $limit, $entity): mixed
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('table');
        $qb->from("App\Entity\\$entity",'table');
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    /**
     * Return total pages
     *
     * @param $limit
     * @param $entity
     * @return float|int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function totalPage($limit, $entity): float|int
    {

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(table.id)');
        $qb->from("App\Entity\\$entity",'table');
        $result = $qb->getQuery()->getSingleScalarResult();
        return $result / $limit;
    }

    /**
     * Find all by a Customer with pagination
     *
     * @param $page
     * @param $limit
     * @param $entity
     * @param $idCustomer
     * @return float|int|mixed|string
     */
    public function findAllByCustomerWithPagination($page, $limit, $entity, $idCustomer): mixed
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('table');
        $qb->from("App\Entity\\$entity",'table')
            ->where("table.customers = $idCustomer");
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    /**
     * Return total pages with CustomerId condition
     *
     * @param $limit
     * @param $entity
     * @param $idCustomer
     * @return float|int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function totalPageByCustomer($limit, $entity, $idCustomer): float|int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(table.id)');
        $qb->from("App\Entity\\$entity",'table')
            ->where("table.customers = $idCustomer");
        $result = $qb->getQuery()->getSingleScalarResult();
        return $result / $limit;
    }
}
