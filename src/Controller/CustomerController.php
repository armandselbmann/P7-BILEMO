<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Services\PaginationService;
use App\Services\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

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
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param ValidatorService $validatorService
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorService $validatorService
    )
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->validatorService = $validatorService;
    }

    /**
     * Get Customer list
     *
     * @param PaginationService $paginationService
     * @param Request $request
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    #[Route('/api/customers', name: 'listCustomer', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour visualiser la liste des clients.')]
    public function listCustomer(PaginationService $paginationService, Request $request): JsonResponse
    {
        $customerList = $paginationService->paginationList($request, Customer::class);
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomerList')
            ->toArray();
        $jsonCustomerList = $this->serializer->serialize($customerList, 'json', $context);
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    /**
     * Get Customer detail
     *
     * @param Customer $customer
     * @return JsonResponse
     */
    #[Route('/api/customers/{id}', name: 'detailCustomer', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour visualiser un client.')]
    public function detailCustomer(Customer $customer): JsonResponse
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomer')
            ->toArray();
        $jsonCustomer = $this->serializer->serialize($customer, 'json', $context);
        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Customer
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/customers', name: 'createCustomer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un client.')]
    public function createCustomer(Request $request): JsonResponse
    {
        $customer = $this->serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setCreatedAt(new \DateTime());
        if($this->validatorService->checkValidation($customer)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($customer), 'json'),
                JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $user = $customer->getUser();
        if($this->validatorService->checkValidation($user)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($user), 'json'),
                JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $passwordHashed = $this->userPasswordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($passwordHashed);
        $user->setRoles(['ROLE_CLIENT']);

        $this->entityManager->persist($user);
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomer')
            ->toArray();
        $jsonCustomer = $this->serializer->serialize($customer, 'json', $context);
        $location = $this->urlGenerator->generate('detailCustomer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update a Customer
     *
     * @param Request $request
     * @param Customer $currentCustomer
     * @return JsonResponse
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
                $this->serializer->serialize($this->validatorService->checkValidation($updatedCustomer), 'json'),
                JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $updatedUser = $updatedCustomer->getUser();
        $updatedPassword = $updatedUser->getPassword();
        if($this->validatorService->checkValidation($updatedUser)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($updatedUser), 'json'),
                JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        if($currentPassword != $updatedPassword) {
            $updatedPasswordHashed = $this->userPasswordHasher->hashPassword($updatedUser, $updatedPassword);
            $updatedUser->setPassword($updatedPasswordHashed);
        }

        $this->entityManager->persist($updatedCustomer);
        $this->entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomer')
            ->toArray();
        $jsonCustomer = $this->serializer->serialize($updatedCustomer, 'json', $context);
        $location = $this->urlGenerator->generate('detailCustomer', ['id' => $updatedCustomer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete a Customer
     *
     * @param Customer $customer
     * @return JsonResponse
     */
    #[Route('/api/customers/{id}', name: 'deleteCustomer', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un client.')]
    public function deleteCustomer(Customer $customer): JsonResponse
    {
        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
