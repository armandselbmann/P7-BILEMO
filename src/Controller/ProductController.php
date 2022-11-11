<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    /**
     * Get Product list
     *
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/products', name: 'listProduct', methods: ['GET'])]
    public function listProduct(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $productList = $productRepository->findAll();
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getProductList')
            ->toArray();
        $jsonProductList = $serializer->serialize($productList, 'json', $context);
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    /**
     * Get Product detail
     *
     * @param Product $product
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'detailProduct', methods: ['GET'])]
    public function detailProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getProduct')
            ->toArray();
        $jsonProduct = $serializer->serialize($product, 'json', $context);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Product
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[Route('/api/products', name: 'createProduct', methods: ['POST'])]
    public function createProduct(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');
        $product->setReleaseDate(new \DateTime());

        $entityManager->persist($product);
        $entityManager->flush();
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getProduct')
            ->toArray();
        $jsonProduct = $serializer->serialize($product, 'json', $context);
        $location = $urlGenerator->generate('detailProduct', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update a Product
     *
     * This method does not allow to modify the images linked to a product.
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param Product $currentProduct
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'updateProduct', methods: ['PUT'])]
    public function updateProduct(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        Product $currentProduct,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse
    {
        $updatedProduct = $serializer->deserialize($request->getContent(),
            Product::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentProduct]);

        $entityManager->persist($updatedProduct);
        $entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getProduct')
            ->toArray();
        $jsonProduct = $serializer->serialize($updatedProduct, 'json', $context);
        $location = $urlGenerator->generate('detailProduct', ['id' => $updatedProduct->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete a Product
     *
     * @param Product $product
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'deleteProduct', methods: ['DELETE'])]
    public function deleteProduct(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
