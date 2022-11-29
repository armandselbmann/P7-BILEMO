<?php

namespace App\Services;

use App\Repository\GlobalRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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
    private TagAwareCacheInterface $cachePool;

    /**
     * @param GlobalRepository $globalRepository
     * @param TagAwareCacheInterface $cachePool
     */
    public function __construct(GlobalRepository $globalRepository, TagAwareCacheInterface $cachePool)
    {
        $this->globalRepository = $globalRepository;
        $this->cachePool = $cachePool;
    }

    /**
     * Pagination for List with no access restriction
     *
     * @param $request
     * @param string $class
     * @return array
     * @throws NoResultException
     * @throws NonUniqueResultException|InvalidArgumentException
     */
    public function paginationList($request, string $class): array
    {
        $page = self::checkPage($request);
        $limit = self::checkLimit($request);

        // Create idCache
        $idCache = stripslashes($class) . "-" . $page . "-" . $limit;
        // Cache retrieval based on idCache or cached request
        $list = $this->cachePool->get($idCache, function (ItemInterface $item) use ($class, $page, $limit) {
            $item->tag(stripslashes($class));
            return $this->globalRepository->findAllWithPagination($page, $limit, $class);
        });

        $totalPage = ceil($this->globalRepository->totalPage($limit, $class));
        self::checkList($list, $totalPage);

        return $list;
    }

    /**
     * Pagination for List with Customer access restriction
     *
     * @param $request
     * @param string $class
     * @param int $idCustomer
     * @return array
     * @throws NoResultException
     * @throws NonUniqueResultException|InvalidArgumentException
     */
    public function paginationListCustomer($request, string $class, int $idCustomer): array
    {
        $page = self::checkPage($request);
        $limit = self::checkLimit($request);

        // Create idCache
        $idCache = stripslashes($class) . "-" . $page . "-" . $limit;
        // Cache retrieval based on idCache or cached request
        $list = $this->cachePool->get($idCache, function (ItemInterface $item) use ($class, $page, $limit, $idCustomer) {
            $item->tag(stripslashes($class));
            return $this->globalRepository->findAllByCustomerWithPagination($page, $limit, $class, $idCustomer);
        });

        $totalPage = ceil($this->globalRepository->totalPageByCustomer($limit, $class, $idCustomer));
        self::checkList($list, $totalPage);

        return $list;
    }

    /**
     * Checking page argument
     *
     * @param $request
     * @return mixed
     */
    private function checkPage($request): mixed
    {
        $page = $request->get('page', self::PAGE_DEFAULT);
        if (empty($page)) { throw new HttpException(400,"Valeur manquante pour l'argument page.");}
        return $page;
    }

    /**
     * Checking limit argument
     *
     * @param $request
     * @return mixed
     */
    private function checkLimit($request): mixed
    {
        $limit = $request->get('limit', self::LIMIT_DEFAULT);
        if (empty($limit)) { throw new HttpException(400,"Valeur manquante pour l'argument limit.");}
        return $limit;
    }

    /**
     * Checking list result to return
     * If nothing to display, throw an exception
     *
     * @param $list
     * @param $totalPage
     * @return void
     */
    private function checkList($list, $totalPage): void
    {
        if (empty($list)) {
            throw new HttpException(404,"Cette page n'existe pas. Voici le nombre total de page : $totalPage");
        }
    }
}