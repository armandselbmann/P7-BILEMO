<?php

namespace App\Controller;

use App\Entity\CustomerUser;
use App\Repository\CustomerRepository;
use App\Repository\CustomerUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
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
     * @var Security
     */
    private Security $security;

    /**
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param Security $security
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        Security $security
    )
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
    }

    /**
     * Get CustomerUser list
     *
     * @param CustomerUserRepository $customerUserRepository
     * @return JsonResponse
     */
    #[Route('/api/customer-users', name: 'listCustomerUser', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisants pour visualiser cette liste d\'utilisateurs.')]
    public function listCustomerUser(CustomerUserRepository $customerUserRepository): JsonResponse
    {
        $customerRoles = $this->security->getUser()->getRoles();
        if(in_array('ROLE_CLIENT', $customerRoles)) {
            $customerId = $this->security->getUser()->getCustomers()->getId();
            $customerUserList = $customerUserRepository->findByCustomers($customerId);
        } else {
            $customerUserList = $customerUserRepository->findAll();
        }

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
     * @param CustomerUserRepository $customerUserRepository
     * @return JsonResponse
     */
    #[Route('/api/customer-users/{id}', name: 'detailCustomerUser', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisants pour visualiser cet d\'utilisateur.')]
    public function detailCustomerUser(CustomerUser $customerUser, CustomerUserRepository $customerUserRepository): JsonResponse
    {
        $customerRoles = $this->security->getUser()->getRoles();
        if(in_array('ROLE_CLIENT', $customerRoles)) {
            $customerId = $this->security->getUser()->getCustomers()->getId();
            $customerUserId = $customerUser->getCustomers()->getId();
            if($customerId != $customerUserId) {
                throw new HttpException(403,'Vous ne pouvez pas accèder à cet utilisateur.');
            }
        }
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
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisants pour créer un utilisateur.')]
    public function createCustomerUser(Request $request, CustomerRepository $customerRepository): JsonResponse
    {
        $customerUser = $this->serializer->deserialize($request->getContent(), CustomerUser::class, 'json');
        $content = $request->toArray();

        $customerRoles = $this->security->getUser()->getRoles();
        if (in_array('ROLE_CLIENT', $customerRoles)) {
            $customerId = $this->security->getUser()->getCustomers()->getId();
        } elseif (empty($customerId) && !empty($content['idCustomer'])) {
                $customerId = $content['idCustomer'];
            } else {
                throw new HttpException(400,'Veuillez saisir un numéro de Client.');
        }

        if (!$customerRepository->findOneById($customerId)) {
            throw new HttpException(404, 'Ce client n\'existe pas.');
        }

        $customerUser->setCustomers($customerRepository->find($customerId));
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
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisants pour modifier un utilisateur.')]
    public function updateCustomerUser(Request $request, CustomerUser $currentCustomerUser): JsonResponse
    {
        $customerRoles = $this->security->getUser()->getRoles();
        if(in_array('ROLE_CLIENT', $customerRoles)) {
            $customerId = $this->security->getUser()->getCustomers()->getId();
            $customerUserId = $currentCustomerUser->getCustomers()->getId();
            if($customerId != $customerUserId) {
                throw new HttpException(403,'Vous ne pouvez pas modifier cet utilisateur.');
            }
        }

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

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
