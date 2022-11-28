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
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

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
     * List all the BileMo products.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @throws NoResultException
     * @throws NonUniqueResultException
     *
     * @OA\Get(
     *      description="List all the BileMo products.",
     *      tags = {"Products"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns product list",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Product_light"))
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response=404, description="This page does not exist. Here is the total number of pages: x"),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page you want to consult.",
     *          @OA\Schema(type="int")
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="The number of elements to be retrieved.",
     *          @OA\Schema(type="int")
     *      )
     * )
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
     * List the characteristics of the specified product.
     *
     * @param Product $product
     * @return JsonResponse
     *
     * @OA\Get(
     *     description="List the characteristics of the specified product.",
     *     tags = {"Products"},
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns product details",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Product"))
     *          )
     *      ),
     *      @OA\Response(response=400, description="Bad Request: This method is not allowed for this route"),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response=404, description="Object not found: Invalid route or resource ID")
     * )
     */
    #[Route('/api/products/{id}', name: 'detailProduct', methods: ['GET'])]
    public function detailProduct(Product $product): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getProduct']);
        $jsonProduct = $this->jmsSerializer->serialize($product, 'json', $context);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new product --- ACCESS RESTRICTED TO EMPLOYEES AND ADMINISTRATORS ---
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Post(
     *     description="Create a new product. Access restricted to employees and administrators.",
     *     tags = {"Products"},
     *     @OA\RequestBody(
     *          description="Product that needs to be added to the catalog.",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Product_post_put"),
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Created - Returns product details",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Product"))
     *          )
     *      ),
     *      @OA\Response(response="400", description="Bad Request: Invalid content"),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response="403", description="Forbidden: You are not allowed to access to this page"),
     * )
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
     * Update a Product --- ACCESS RESTRICTED TO EMPLOYEES AND ADMINISTRATORS ---
     *
     * This method does not allow to modify the images linked to a product.
     *
     * @param Request $request
     * @param Product $currentProduct
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Put(
     *     description="Update a product. This operation does not allow to modify the images linked to a product. Access restricted to employees and administrators.",
     *     tags = {"Products"},
     *     @OA\RequestBody(
     *          description="Properties of the product object that can be update.",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Product_post_put"),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns the updated product",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Product"))
     *          )
     *      ),
     *      @OA\Response(response="400", description="Bad Request: This method is not allowed for this route OR Could not decode JSON, syntax error - malformed JSON. OR The JSON sent contains invalid data."),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response="403", description="Forbidden: You are not allowed to access to this page"),
     * )
     */
    #[Route('/api/products/{id}', name: 'updateProduct', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un produit.')]
    public function updateProduct(Request $request, Product $currentProduct): JsonResponse
    {
        $updatedProduct = $this->serializer->deserialize($request->getContent(),
            Product::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentProduct]);

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
     * Delete the specified product --- ACCESS RESTRICTED TO ADMINISTRATORS ---
     *
     * @param Product $product
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Delete(
     *     description="Delete the specified product. Access restricted to administrators.",
     *     tags = {"Products"},
     *     @OA\Response(response=204, description="Successful operation: No-Content"),
     *     @OA\Response(response="400", description="Bad Request: This method is not allowed for this route"),
     *     @OA\Response(response="401", description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *     @OA\Response(response="403", description="Forbidden: You are not allowed to access to this page"),
     *     @OA\Response(response="404", description="Object not found: Invalid route or resource ID")
     * )
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
