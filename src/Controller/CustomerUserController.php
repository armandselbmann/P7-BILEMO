<?php

namespace App\Controller;

use App\Entity\CustomerUser;
use App\Repository\CustomerRepository;
use App\Services\ExistingObjectConstructor;
use App\Services\PaginationService;
use App\Services\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


/**
 * CustomerUser Controller methods
 */
class CustomerUserController extends AbstractController
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
     * @var Security
     */
    private Security $security;
    /**
     * @var ValidatorService
     */
    private ValidatorService $validatorService;
    /**
     * @var CustomerRepository
     */
    private CustomerRepository $customerRepository;
    /**
     * @var PaginationService
     */
    private PaginationService $paginationService;
    /**
     * @var TagAwareCacheInterface
     */
    private TagAwareCacheInterface $cachePool;
    /**
     * @var ExistingObjectConstructor
     */
    private ExistingObjectConstructor $objectConstructor;

    /**
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param Security $security
     * @param ValidatorService $validatorService
     * @param CustomerRepository $customerRepository
     * @param PaginationService $paginationService
     * @param TagAwareCacheInterface $cachePool
     * @param ExistingObjectConstructor $objectConstructor
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        Security $security,
        ValidatorService $validatorService,
        CustomerRepository $customerRepository,
        PaginationService $paginationService,
        TagAwareCacheInterface $cachePool,
        ExistingObjectConstructor $objectConstructor
    )
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
        $this->validatorService = $validatorService;
        $this->customerRepository = $customerRepository;
        $this->paginationService = $paginationService;
        $this->cachePool = $cachePool;
        $this->objectConstructor = $objectConstructor;
    }

    /**
     * List all the customer users.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException|InvalidArgumentException
     *
     * @OA\Get(
     *      description="List all the customer users. A restriction is applied on clients. Customers have access to customer users linked to them.",
     *      tags = {"CustomerUsers"},
     *      @OA\Response(
     *          response=200,
     *          description="Return the customer user list",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/CustomerUser_light"))
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
    #[Route('/api/customer-users', name: 'listCustomerUser', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisants pour visualiser cette liste d\'utilisateurs.')]
    public function listCustomerUser(Request $request): JsonResponse
    {
        $customerRoles = $this->security->getUser()->getRoles();
        if(in_array('ROLE_CLIENT', $customerRoles)) {
            $customerId = $this->security->getUser()->getCustomers()->getId();
            $customerUserList = $this->paginationService->paginationListCustomer($request, CustomerUser::class, $customerId);
        } else {
            $customerUserList = $this->paginationService->paginationList($request, CustomerUser::class);
        }

        $context = SerializationContext::create()->setGroups(['getCustomerUserList']);
        $jsonCustomerUserList = $this->serializer->serialize($customerUserList, 'json', $context);
        return new JsonResponse($jsonCustomerUserList, Response::HTTP_OK, [], true);
    }

    /**
     * List the characteristics of the specified customer user.
     *
     * @param CustomerUser $customerUser
     * @return JsonResponse
     *
     * @OA\Get(
     *     description="List the characteristics of the specified customer user. Customers are restricted to their own customer users.",
     *     tags = {"CustomerUsers"},
     *     @OA\Response(
     *          response=200,
     *          description="Return customer user detail",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/CustomerUser"))
     *          )
     *      ),
     *      @OA\Response(response=400, description="Bad Request: This method is not allowed for this route"),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response=404, description="Object not found: Invalid route or resource ID")
     * )
     */
    #[Route('/api/customer-users/{id}', name: 'detailCustomerUser', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisants pour visualiser cet d\'utilisateur.')]
    public function detailCustomerUser(CustomerUser $customerUser): JsonResponse
    {
        $customerRoles = $this->security->getUser()->getRoles();
        if(in_array('ROLE_CLIENT', $customerRoles)) {
            $customerId = $this->security->getUser()->getCustomers()->getId();
            $customerUserId = $customerUser->getCustomers()->getId();
            if($customerId != $customerUserId) {
                throw new HttpException(403,'Vous ne pouvez pas accèder à cet utilisateur.');
            }
        }
        $context = SerializationContext::create()->setGroups(['getCustomerUser']);
        $jsonCustomerUser = $this->serializer->serialize($customerUser, 'json', $context);
        return new JsonResponse($jsonCustomerUser, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Customer user
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Post(
     *     description="Create a new customer user. For Customer : The customer user will automatically be linked to you.",
     *     tags = {"CustomerUsers"},
     *     @OA\RequestBody(
     *          description="Customer user that needs to be added.",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CustomerUser_post_put")
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Created - Returns customer user details",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/CustomerUser"))
     *          )
     *      ),
     *      @OA\Response(response="400", description="Bad Request: Invalid content"),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Parameter(
     *          name="idCustomer",
     *          in="query",
     *          description="For Employees and Administrators only. Enter the id of the Customer you want to linked to this customer user.",
     *          @OA\Schema(type="int")
     *      )
     */
    #[Route('/api/customer-users', name: 'createCustomerUser', methods: ['POST'])]
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisants pour créer un utilisateur.')]
    public function createCustomerUser(Request $request): JsonResponse
    {
        $customerUser = $this->serializer->deserialize($request->getContent(), CustomerUser::class, 'json');

        $customerRoles = $this->security->getUser()->getRoles();
        if (in_array('ROLE_CLIENT', $customerRoles)) {
            $customerId = $this->security->getUser()->getCustomers()->getId();
        } else {
            $customerId = $request->get('idCustomer');
        }
        if(empty($customerId)) {
            throw new HttpException(400, 'Veuillez saisir un numéro de Client.');
        }
        if (!$this->customerRepository->findOneById($customerId)) {
            throw new HttpException(404, 'Ce client n\'existe pas.');
        }

        $customerUser->setCustomers($this->customerRepository->findOneById($customerId));
        $customerUser->setCreatedAt(new \DateTime());

        if($this->validatorService->checkValidation($customerUser)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($customerUser), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }
        $this->entityManager->persist($customerUser);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(CustomerUser::class)]);

        $context = SerializationContext::create()->setGroups(['getCustomerUser']);
        $jsonCustomerUser = $this->serializer->serialize($customerUser, 'json', $context);
        $location = $this->urlGenerator->generate('detailCustomerUser', ['id' => $customerUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomerUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update a Customer user
     *
     * @param Request $request
     * @param CustomerUser $currentCustomerUser
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Put(
     *     description="Update a Customer user. For Employees and Administrators : It's not possible to update a idCustomer linked to a CustomerUser.",
     *     tags = {"CustomerUsers"},
     *     @OA\RequestBody(
     *          description="Properties of an customer user that can be update.",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CustomerUser_post_put"),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns the updated customer user",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/CustomerUser"))
     *          )
     *      ),
     *      @OA\Response(response="400", description="Bad Request: This method is not allowed for this route OR Could not decode JSON, syntax error - malformed JSON. OR The JSON sent contains invalid data."),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     * )
     */
    #[Route('/api/customer-users/{id}', name: 'updateCustomerUser', methods: ['PUT'])]
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisants pour modifier un utilisateur.')]
    public function updateCustomerUser(Request $request, CustomerUser $currentCustomerUser): JsonResponse
    {
        $newCustomerUser = $this->serializer->deserialize($request->getContent(), CustomerUser::class, 'json');
        $currentCustomerUser = $this->objectConstructor->customerUserConstructor($newCustomerUser, $currentCustomerUser);

        $customerRoles = $this->security->getUser()->getRoles();
        if(in_array('ROLE_CLIENT', $customerRoles)) {
            $customerId = $this->security->getUser()->getCustomers()->getId();
            $customerUserId = $currentCustomerUser->getCustomers()->getId();
            if($customerId != $customerUserId) {
                throw new HttpException(403,'Vous ne pouvez pas modifier cet utilisateur.');
            }
        }
        if($this->validatorService->checkValidation($currentCustomerUser)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($currentCustomerUser), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(CustomerUser::class)]);

        $context = SerializationContext::create()->setGroups(['getCustomerUser']);
        $jsonCustomerUser = $this->serializer->serialize($currentCustomerUser, 'json', $context);
        $location = $this->urlGenerator->generate('detailCustomerUser', ['id' => $currentCustomerUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomerUser, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete a CustomerUser
     *
     * @param CustomerUser $customerUser
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Delete(
     *     description="Delete the specified customer user",
     *     tags = {"CustomerUsers"},
     *     @OA\Response(response=204, description="Successful operation: No-Content"),
     *     @OA\Response(response="400", description="Bad Request: This method is not allowed for this route"),
     *     @OA\Response(response="401", description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *     @OA\Response(response="404", description="Object not found: Invalid route or resource ID")
     * )
     */
    #[Route('/api/customer-users/{id}', name: 'deleteCustomerUser', methods: ['DELETE'])]
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisants pour supprimer un utilisateur.')]
    public function deleteCustomerUser(CustomerUser $customerUser): JsonResponse
    {
        $customerRoles = $this->security->getUser()->getRoles();
        if(in_array('ROLE_CLIENT', $customerRoles)) {
            $customerId = $this->security->getUser()->getCustomers()->getId();
            $customerUserId = $customerUser->getCustomers()->getId();
            if($customerId != $customerUserId) {
                throw new HttpException(403,'Vous ne pouvez pas supprimer cet utilisateur.');
            }
        }
        $this->entityManager->remove($customerUser);
        $this->entityManager->flush();


        $this->cachePool->invalidateTags([stripslashes(CustomerUser::class)]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
