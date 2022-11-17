<?php

namespace App\Services;

use App\Repository\GlobalRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Service for Pagination
 */
class PaginationService {

    const PAGE_DEFAULT = 1;
    const LIMIT_DEFAULT = 2;

    /**
     * @var GlobalRepository
     */
    private GlobalRepository $globalRepository;

    /**
     * @param GlobalRepository $globalRepository
     */
    public function __construct(GlobalRepository $globalRepository)
    {
        $this->globalRepository = $globalRepository;
    }

    /**
     * Pagination for List with no access restriction
     *
     * @param $request
     * @param string $class
     * @return float|int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function paginationList($request, string $class): mixed
    {
        $page = $request->get('page', self::PAGE_DEFAULT);
        if (empty($page)) { throw new HttpException(400,"Valeur manquante pour l'argument page.");}
        $limit = $request->get('limit', self::LIMIT_DEFAULT);
        if (empty($limit)) { throw new HttpException(400,"Valeur manquante pour l'argument limit.");}

        $list = $this->globalRepository->findAllWithPagination($page, $limit, $class);
        $totalPage = ceil($this->globalRepository->totalPage($limit, $class));

        if (empty($list)) {
            throw new HttpException(404,"Cette page n'existe pas. Voici le nombre total de page : $totalPage");
        }
        return $list;
    }

    /**
     * Pagination for List with Customer access restriction
     *
     * @param $request
     * @param string $class
     * @param int $idCustomer
     * @return float|int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function paginationListCustomer($request, string $class, int $idCustomer): mixed
    {
        $page = $request->get('page', self::PAGE_DEFAULT);
        if (empty($page)) { throw new HttpException(400,"Valeur manquante pour l'argument page.");}
        $limit = $request->get('limit', self::LIMIT_DEFAULT);
        if (empty($limit)) { throw new HttpException(400,"Valeur manquante pour l'argument limit.");}

        $list = $this->globalRepository->findAllByCustomerWithPagination($page, $limit, $class, $idCustomer);
        $totalPage = ceil($this->globalRepository->totalPageByCustomer($limit, $class, $idCustomer));

        if (empty($list)) {
            throw new HttpException(404,"Cette page n'existe pas. Voici le nombre total de page : $totalPage");
        }
        return $list;
    }
}