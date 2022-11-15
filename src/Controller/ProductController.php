<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Product Controller methods
 */
class ProductController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Get Product list
     *
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    #[Route('/api/products', name: 'listProduct', methods: ['GET'])]
    public function listProduct(ProductRepository $productRepository): JsonResponse
    {
        $productList = $productRepository->findAll();
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getProductList')
            ->toArray();
        $jsonProductList = $this->serializer->serialize($productList, 'json', $context);
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
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getProduct')
            ->toArray();
        $jsonProduct = $this->serializer->serialize($product, 'json', $context);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Product
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/products', name: 'createProduct', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un produit.')]
    public function createProduct(Request $request): JsonResponse
    {
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json');
        $product->setReleaseDate(new \DateTime());

        $this->entityManager->persist($product);
        $this->entityManager->flush();
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getProduct')
            ->toArray();
        $jsonProduct = $this->serializer->serialize($product, 'json', $context);
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
     */
    #[Route('/api/products/{id}', name: 'updateProduct', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un produit.')]
    public function updateProduct(Request $request, Product $currentProduct): JsonResponse
    {
        $updatedProduct = $this->serializer->deserialize($request->getContent(),
            Product::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentProduct]);

        $this->entityManager->persist($updatedProduct);
        $this->entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getProduct')
            ->toArray();
        $jsonProduct = $this->serializer->serialize($updatedProduct, 'json', $context);
        $location = $this->urlGenerator->generate('detailProduct', ['id' => $updatedProduct->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete a Product
     *
     * @param Product $product
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'deleteProduct', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un produit.')]
    public function deleteProduct(Product $product): JsonResponse
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
