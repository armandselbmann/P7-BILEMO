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
     * Get CustomerUser list
     *
     * @param CustomerUserRepository $customerUserRepository
     * @return JsonResponse
     */
    #[Route('/api/customer-users', name: 'listCustomerUser', methods: ['GET'])]
    public function listCustomerUser(CustomerUserRepository $customerUserRepository): JsonResponse
    {
        $customerUserList = $customerUserRepository->findAll();
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomerUserList')
            ->toArray();
        $jsonCustomerUserList = $this->serializer->serialize($customerUserList, 'json', $context);
        return new JsonResponse($jsonCustomerUserList, Response::HTTP_OK, [], true);
    }

    /**
     * Get CustomerUser detail
     *
     * @param CustomerUser $customerUser
     * @return JsonResponse
     */
    #[Route('/api/customer-users/{id}', name: 'detailCustomerUser', methods: ['GET'])]
    public function detailCustomerUser(CustomerUser $customerUser): JsonResponse
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomerUser')
            ->toArray();
        $jsonCustomerUser = $this->serializer->serialize($customerUser, 'json', $context);
        return new JsonResponse($jsonCustomerUser, Response::HTTP_OK, [], true);
    }

    /**
     * Create a CustomerUser
     *
     * We pass the id Customer linked to this CustomerUser in the body request.
     *
     * @param Request $request
     * @param CustomerRepository $customerRepository
     * @return JsonResponse
     */
    #[Route('/api/customer-users', name: 'createCustomerUser', methods: ['POST'])]
    public function createCustomerUser(Request $request, CustomerRepository $customerRepository): JsonResponse
    {
        $customerUser = $this->serializer->deserialize($request->getContent(), CustomerUser::class, 'json');
        $content = $request->toArray();
        $idCustomer = $content['idCustomer'] ?? -1;

        $customerUser->setCustomers($customerRepository->find($idCustomer));
        $customerUser->setCreatedAt(new \DateTime());

        $this->entityManager->persist($customerUser);
        $this->entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomerUser')
            ->toArray();
        $jsonCustomerUser = $this->serializer->serialize($customerUser, 'json', $context);
        $location = $this->urlGenerator->generate('detailCustomerUser', ['id' => $customerUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomerUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update a Customer
     *
     * @param Request $request
     * @param CustomerUser $currentCustomerUser
     * @return JsonResponse
     */
    #[Route('/api/customer-users/{id}', name: 'updateCustomerUser', methods: ['PUT'])]
    public function updateCustomerUser(Request $request, CustomerUser $currentCustomerUser): JsonResponse
    {
        $updatedCustomerUser = $this->serializer->deserialize($request->getContent(),
            CustomerUser::class,
            'json',
            [ AbstractNormalizer::OBJECT_TO_POPULATE => $currentCustomerUser]);

        $this->entityManager->persist($updatedCustomerUser);
        $this->entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomerUser')
            ->toArray();
        $jsonCustomerUser = $this->serializer->serialize($updatedCustomerUser, 'json', $context);
        $location = $this->urlGenerator->generate('detailCustomerUser', ['id' => $updatedCustomerUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomerUser, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete a CustomerUser
     *
     * @param CustomerUser $customerUser
     * @return JsonResponse
     */
    #[Route('/api/customer-users/{id}', name: 'deleteCustomerUser', methods: ['DELETE'])]
    public function deleteCustomerUser(CustomerUser $customerUser): JsonResponse
    {
        $this->entityManager->remove($customerUser);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
