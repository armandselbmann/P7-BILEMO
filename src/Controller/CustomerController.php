<?php

namespace App\Controller;

use App\Entity\Customer;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


/**
 * Customer Controller methods
 */
class CustomerController extends AbstractController
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
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;
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
     * @var \JMS\Serializer\SerializerInterface
     */
    private \JMS\Serializer\SerializerInterface $jmsSerializer;

    /**
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param ValidatorService $validatorService
     * @param PaginationService $paginationService
     * @param TagAwareCacheInterface $cachePool
     * @param \JMS\Serializer\SerializerInterface $jmsSerializer
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorService $validatorService,
        PaginationService $paginationService,
        TagAwareCacheInterface $cachePool,
        \JMS\Serializer\SerializerInterface $jmsSerializer
    )
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->validatorService = $validatorService;
        $this->paginationService = $paginationService;
        $this->cachePool = $cachePool;
        $this->jmsSerializer = $jmsSerializer;
    }

    /**
     * List all the customer --- ACCESS RESTRICTED TO EMPLOYEES AND ADMINISTRATORS ---
     *
     * @param Request $request
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException|InvalidArgumentException
     *
     * @OA\Get(
     *      description="List all the customers. Access restricted to employees and administrators.",
     *      tags = {"Customers"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns customer list",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Customer_light"))
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response=403, description="Forbidden: access denied"),
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
    #[Route('/api/customers', name: 'listCustomer', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour visualiser la liste des clients.')]
    public function listCustomer(Request $request): JsonResponse
    {
        $customerList = $this->paginationService->paginationList($request, Customer::class);
        $context = SerializationContext::create()->setGroups(['getCustomerList']);
        $jsonCustomerList = $this->jmsSerializer->serialize($customerList, 'json', $context);
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    /**
     * List the characteristics of the specified customer --- ACCESS RESTRICTED TO EMPLOYEES AND ADMINISTRATORS ---
     *
     * @param Customer $customer
     * @return JsonResponse
     *
     * @OA\Get(
     *     description="List the characteristics of the specified customer. Access restricted to employees and administrators.",
     *     tags = {"Customers"},
     *     @OA\Response(
     *          response=200,
     *          description="Returns customer detail",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Customer"))
     *          )
     *      ),
     *      @OA\Response(response=400, description="Bad Request: This method is not allowed for this route"),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response=403, description="Forbidden: access denied"),
     *      @OA\Response(response=404, description="Object not found: Invalid route or resource ID")
     * )
     */
    #[Route('/api/customers/{id}', name: 'detailCustomer', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour visualiser un client.')]
    public function detailCustomer(Customer $customer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getCustomer']);
        $jsonCustomer = $this->jmsSerializer->serialize($customer, 'json', $context);
        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new customer --- ACCESS RESTRICTED TO EMPLOYEES AND ADMINISTRATORS ---
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Post(
     *     description="Create a new customer. Access restricted to employees and administrators.",
     *     tags = {"Customers"},
     *     @OA\RequestBody(
     *          description="Customer that needs to be added.",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Customer_post_put"),
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Created - Returns employee details",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Customer"))
     *          )
     *      ),
     *      @OA\Response(response="400", description="Bad Request: Invalid content"),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response="403", description="Forbidden: You are not allowed to access to this page"),
     * )
     */
    #[Route('/api/customers', name: 'createCustomer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un client.')]
    public function createCustomer(Request $request): JsonResponse
    {
        $customer = $this->jmsSerializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setCreatedAt(new \DateTime());
        if($this->validatorService->checkValidation($customer)) {
            return new JsonResponse(
                $this->jmsSerializer->serialize($this->validatorService->checkValidation($customer), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        $user = $customer->getUser();
        if($this->validatorService->checkValidation($user)) {
            return new JsonResponse(
                $this->jmsSerializer->serialize($this->validatorService->checkValidation($user), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }
        $passwordHashed = $this->userPasswordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($passwordHashed);
        $user->setRoles(['ROLE_CLIENT']);
        $user->setCustomers($customer);

        $this->entityManager->persist($user);
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Customer::class)]);

        $context = SerializationContext::create()->setGroups(['getCustomer']);
        $jsonCustomer = $this->jmsSerializer->serialize($customer, 'json', $context);
        $location = $this->urlGenerator->generate('detailCustomer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update a Customer --- ACCESS RESTRICTED TO EMPLOYEES AND ADMINISTRATORS ---
     *
     * @param Request $request
     * @param Customer $currentCustomer
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Put(
     *     description="Update a Customer. This operation does not allow to modify the images linked to a product. Access restricted to employees and administrators.",
     *     tags = {"Customers"},
     *     @OA\RequestBody(
     *          description="Properties of an customer that can be update.",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Customer_post_put"),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns the updated customer",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Customer"))
     *          )
     *      ),
     *      @OA\Response(response="400", description="Bad Request: This method is not allowed for this route OR Could not decode JSON, syntax error - malformed JSON. OR The JSON sent contains invalid data."),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response="403", description="Forbidden: You are not allowed to access to this page"),
     * )
     */
    #[Route('/api/customers/{id}', name: 'updateCustomer', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un client.')]
    public function updateCustomer(Request $request, Customer $currentCustomer): JsonResponse
    {
        $currentUser = $currentCustomer->getUser();
        $currentPassword = $currentUser->getPassword();

        $updatedCustomer = $this->serializer->deserialize($request->getContent(),
            Customer::class,
            'json',
            [ AbstractNormalizer::OBJECT_TO_POPULATE => $currentCustomer, AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true]);
        if($this->validatorService->checkValidation($updatedCustomer)) {
            return new JsonResponse(
                $this->jmsSerializer->serialize($this->validatorService->checkValidation($updatedCustomer), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        $updatedUser = $updatedCustomer->getUser();
        $updatedPassword = $updatedUser->getPassword();
        if($this->validatorService->checkValidation($updatedUser)) {
            return new JsonResponse(
                $this->jmsSerializer->serialize($this->validatorService->checkValidation($updatedUser), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }
        if($currentPassword != $updatedPassword) {
            $updatedPasswordHashed = $this->userPasswordHasher->hashPassword($updatedUser, $updatedPassword);
            $updatedUser->setPassword($updatedPasswordHashed);
        }

        $this->entityManager->persist($updatedCustomer);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Customer::class)]);

        $context = SerializationContext::create()->setGroups(['getCustomer']);
        $jsonCustomer = $this->jmsSerializer->serialize($updatedCustomer, 'json', $context);
        $location = $this->urlGenerator->generate('detailCustomer', ['id' => $updatedCustomer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete a Customer --- ACCESS RESTRICTED TO ADMINISTRATORS ---
     *
     * @param Customer $customer
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Delete(
     *     description="Delete the specified customer. Access restricted to administrators.",
     *     tags = {"Customers"},
     *     @OA\Response(response=204, description="Successful operation: No-Content"),
     *     @OA\Response(response="400", description="Bad Request: This method is not allowed for this route"),
     *     @OA\Response(response="401", description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *     @OA\Response(response="403", description="Forbidden: You are not allowed to access to this page"),
     *     @OA\Response(response="404", description="Object not found: Invalid route or resource ID")
     * )
     */
    #[Route('/api/customers/{id}', name: 'deleteCustomer', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un client.')]
    public function deleteCustomer(Customer $customer): JsonResponse
    {
        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Customer::class)]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
