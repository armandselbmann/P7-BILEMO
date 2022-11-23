<?php

namespace App\Controller;

use App\Entity\Product;
use App\Services\PaginationService;
use App\Services\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JMS\Serializer\SerializationContext;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


/**
 * Product Controller methods
 */
class ProductController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;
    /**
     * @var ValidatorService
     */
    private ValidatorService $validatorService;
    /**
     * @var PaginationService
     */
    private PaginationService $paginationService;
    /**
     * @var TagAwareCacheInterface
     */
    private TagAwareCacheInterface $cachePool;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    private \JMS\Serializer\SerializerInterface $jmsSerializer;


    /**
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorService $validatorService
     * @param PaginationService $paginationService
     * @param TagAwareCacheInterface $cachePool
     * @param \JMS\Serializer\SerializerInterface $jmsSerializer
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        ValidatorService $validatorService,
        PaginationService $paginationService,
        TagAwareCacheInterface $cachePool,
        \JMS\Serializer\SerializerInterface $jmsSerializer
    )
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->validatorService = $validatorService;
        $this->paginationService = $paginationService;
        $this->cachePool = $cachePool;
        $this->jmsSerializer = $jmsSerializer;
    }

    /**
     * Get Product list
     *
     * @param Request $request
     * @return JsonResponse
     * @throws NonUniqueResultException|InvalidArgumentException|NoResultException
     */
    #[Route('/api/products', name: 'listProduct', methods: ['GET'])]
    public function listProduct(Request $request): JsonResponse
    {
        $productList = $this->paginationService->paginationList($request, Product::class);
        $context = SerializationContext::create()->setGroups(['getProductList']);
        $jsonProductList = $this->jmsSerializer->serialize($productList, 'json', $context);
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    /**
     * Get Product detail
     *
     * @param Product $product
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'detailProduct', methods: ['GET'])]
    public function detailProduct(Product $product): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getProduct']);
        $jsonProduct = $this->jmsSerializer->serialize($product, 'json', $context);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Product
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/api/products', name: 'createProduct', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un produit.')]
    public function createProduct(Request $request): JsonResponse
    {
        $product = $this->jmsSerializer->deserialize($request->getContent(), Product::class, 'json');
        $product->setReleaseDate(new \DateTime());

        if($this->validatorService->checkValidation($product)) {
            return new JsonResponse(
                $this->jmsSerializer->serialize($this->validatorService->checkValidation($product), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Product::class)]);

        $context = SerializationContext::create()->setGroups(['getProduct']);
        $jsonProduct = $this->jmsSerializer->serialize($product, 'json', $context);
        $location = $this->urlGenerator->generate('detailProduct', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update a Product
     *
     * This method does not allow to modify the images linked to a product.
     *
     * @param Request $request
     * @param Product $currentProduct
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/api/products/{id}', name: 'updateProduct', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un produit.')]
    public function updateProduct(Request $request, Product $currentProduct): JsonResponse
    {
        $updatedProduct = $this->serializer->deserialize($request->getContent(),
            Product::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE, $currentProduct]);

        if($this->validatorService->checkValidation($updatedProduct)) {
            return new JsonResponse(
                $this->jmsSerializer->serialize($this->validatorService->checkValidation($updatedProduct), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        $this->entityManager->persist($updatedProduct);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Product::class)]);

        $context = SerializationContext::create()->setGroups(['getProduct']);
        $jsonProduct = $this->jmsSerializer->serialize($updatedProduct, 'json', $context);
        $location = $this->urlGenerator->generate('detailProduct', ['id' => $updatedProduct->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete a Product
     *
     * @param Product $product
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/api/products/{id}', name: 'deleteProduct', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un produit.')]
    public function deleteProduct(Product $product): JsonResponse
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Product::class)]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
