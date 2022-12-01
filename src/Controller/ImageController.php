<?php
namespace App\Controller;

use App\Entity\Image;
use App\Services\PaginationService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Image Controller methods
 */
class ImageController extends AbstractController
{
    /**
     * @var PaginationService
     */
    private PaginationService $paginationService;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param SerializerInterface $serializer
     * @param PaginationService $paginationService
     */
    public function __construct(
        SerializerInterface $serializer,
        PaginationService $paginationService,
    )
    {
        $this->serializer = $serializer;
        $this->paginationService = $paginationService;
    }

    /**
     * List all the images
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @throws NoResultException
     * @throws NonUniqueResultException
     *
     * @OA\Get(
     *      description="List all the images.",
     *      tags = {"Images"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns image list",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Image"))
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
    #[Route('/api/images', name: 'listImage', methods: ['GET'])]
    public function listImage(Request $request): JsonResponse
    {
        $imageList = $this->paginationService->paginationList($request, Image::class);
        $context = SerializationContext::create()->setGroups(['getImage']);
        $jsonProductList = $this->serializer->serialize($imageList, 'json', $context);
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    /**
     * List the characteristics of the specified image.
     *
     * @param Image $image
     * @return JsonResponse
     *
     * @OA\Get(
     *     description="List the characteristics of the specified image.",
     *     tags = {"Images"},
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns image details",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Image"))
     *          )
     *      ),
     * @OA\Response(response=400, description="Bad Request: This method is not allowed for this route"),
     * @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     * @OA\Response(response=404, description="Object not found: Invalid route or resource ID")
     * )
     */
    #[Route('/api/images/{id}', name: 'detailImage', methods: ['GET'])]
    public function detailImage(Image $image): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getImage']);
        $jsonProduct = $this->serializer->serialize($image, 'json', $context);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

}
