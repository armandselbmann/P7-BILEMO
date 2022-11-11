<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
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

class CustomerController extends AbstractController
{
    /**
     * Get Customer list
     *
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/customers', name: 'listCustomer', methods: ['GET'])]
    public function listCustomer(CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customerList = $customerRepository->findAll();
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomerList')
            ->toArray();
        $jsonCustomerList = $serializer->serialize($customerList, 'json', $context);
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    /**
     * Get Customer detail
     *
     * @param Customer $customer
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/customers/{id}', name: 'detailCustomer', methods: ['GET'])]
    public function detailCustomer(Customer $customer, SerializerInterface $serializer): JsonResponse
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomer')
            ->toArray();
        $jsonCustomer = $serializer->serialize($customer, 'json', $context);
        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Customer
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return JsonResponse
     */
    #[Route('/api/customers', name: 'createCustomer', methods: ['POST'])]
    public function createCustomer(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordHasherInterface $userPasswordHasher
    ): JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setCreatedAt(new \DateTime());

        $user = $customer->getUser();
        $passwordHashed = $userPasswordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($passwordHashed);
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->persist($customer);
        $entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomer')
            ->toArray();
        $jsonCustomer = $serializer->serialize($customer, 'json', $context);
        $location = $urlGenerator->generate('detailCustomer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update a Customer
     *
     * @param Request $request
     * @param Customer $currentCustomer
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[Route('/api/customers/{id}', name: 'updateCustomer', methods: ['PUT'])]
    public function updateCustomer(
        Request $request,
        Customer $currentCustomer,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse
    {
        $currentUser = $currentCustomer->getUser();
        $currentPassword = $currentUser->getPassword();

        $updatedCustomer = $serializer->deserialize($request->getContent(),
            Customer::class,
            'json',
            [ AbstractNormalizer::OBJECT_TO_POPULATE => $currentCustomer, AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true]);

        $updatedUser = $updatedCustomer->getUser();
        $updatedPassword = $updatedUser->getPassword();

        if($currentPassword != $updatedPassword) {
            $updatedPasswordHashed = $userPasswordHasher->hashPassword($updatedUser, $updatedPassword);
            $updatedUser->setPassword($updatedPasswordHashed);
        }

        $entityManager->persist($updatedCustomer);
        $entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getCustomer')
            ->toArray();
        $jsonCustomer = $serializer->serialize($updatedCustomer, 'json', $context);
        $location = $urlGenerator->generate('detailCustomer', ['id' => $updatedCustomer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete a Customer
     *
     * @param Customer $customer
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/customers/{id}', name: 'deleteCustomer', methods: ['DELETE'])]
    public function deleteCustomer(Customer $customer, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($customer);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
