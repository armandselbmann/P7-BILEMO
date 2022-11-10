<?php

namespace App\Controller;

use App\Entity\CustomerUser;
use App\Repository\CustomerRepository;
use App\Repository\CustomerUserRepository;
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

class CustomerUserController extends AbstractController
{
    /**
     * Get CustomerUser list
     *
     * @param CustomerUserRepository $customerUserRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/customer-users', name: 'listCustomerUser', methods: ['GET'])]
    public function listCustomerUser(CustomerUserRepository $customerUserRepository, SerializerInterface $serializer): JsonResponse
    {
        $customerUserList = $customerUserRepository->findAll();
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomerUserList')
            ->toArray();
        $jsonCustomerUserList = $serializer->serialize($customerUserList, 'json', $context);
        return new JsonResponse($jsonCustomerUserList, Response::HTTP_OK, [], true);
    }

    /**
     * Get CustomerUser detail
     *
     * @param CustomerUser $customerUser
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/customer-users/{id}', name: 'detailCustomerUser', methods: ['GET'])]
    public function detailCustomerUser(CustomerUser $customerUser, SerializerInterface $serializer): JsonResponse
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomerUser')
            ->toArray();
        $jsonCustomerUser = $serializer->serialize($customerUser, 'json', $context);
        return new JsonResponse($jsonCustomerUser, Response::HTTP_OK, [], true);
    }

    /**
     * Create a CustomerUser
     *
     * We pass the id Customer linked to this CustomerUser in the body request.
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param CustomerRepository $customerRepository
     * @return JsonResponse
     */
    #[Route('/api/customer-users', name: 'createCustomerUser', methods: ['POST'])]
    public function createCustomerUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        CustomerRepository $customerRepository
    ): JsonResponse
    {
        $customerUser = $serializer->deserialize($request->getContent(), CustomerUser::class, 'json');
        $content = $request->toArray();
        $idCustomer = $content['idCustomer'] ?? -1;

        $customerUser->setCustomers($customerRepository->find($idCustomer));
        $customerUser->setCreatedAt(new \DateTime());

        $entityManager->persist($customerUser);
        $entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomerUser')
            ->toArray();
        $jsonCustomerUser = $serializer->serialize($customerUser, 'json', $context);
        $location = $urlGenerator->generate('detailCustomerUser', ['id' => $customerUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomerUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update a Customer
     *
     * @param Request $request
     * @param CustomerUser $currentCustomerUser
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/customer-users/{id}', name: 'updateCustomerUser', methods: ['PUT'])]
    public function updateCustomerUser(
        Request $request,
        CustomerUser $currentCustomerUser,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        $updatedCustomerUser = $serializer->deserialize($request->getContent(),
            CustomerUser::class,
            'json',
            [ AbstractNormalizer::OBJECT_TO_POPULATE => $currentCustomerUser]);

        $entityManager->persist($updatedCustomerUser);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a CustomerUser
     *
     * @param CustomerUser $customerUser
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/customer-users/{id}', name: 'deleteCustomerUser', methods: ['DELETE'])]
    public function deleteCustomerUser(CustomerUser $customerUser, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($customerUser);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
