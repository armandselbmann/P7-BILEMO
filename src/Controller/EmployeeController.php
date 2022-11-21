<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Services\PaginationService;
use App\Services\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Cache\InvalidArgumentException;
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
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Employee Controller methods
 */
class EmployeeController extends AbstractController
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
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param ValidatorService $validatorService
     * @param PaginationService $paginationService
     * @param TagAwareCacheInterface $cachePool
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorService $validatorService,
        PaginationService $paginationService,
        TagAwareCacheInterface $cachePool
    )
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->validatorService = $validatorService;
        $this->paginationService = $paginationService;
        $this->cachePool = $cachePool;
    }

    /**
     * Get Employee list
     *
     * @param Request $request
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException|InvalidArgumentException
     */
    #[Route('/api/employees', name: 'listEmployee', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour visualiser la liste des employé(e)s.')]
    public function listEmployee(Request $request): JsonResponse
    {
        $employeeList = $this->paginationService->paginationList($request, Employee::class);
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getEmployeeList')
            ->toArray();
        $jsonEmployeeList = $this->serializer->serialize($employeeList, 'json', $context);
        return new JsonResponse($jsonEmployeeList, Response::HTTP_OK, [], true);
    }

    /**
     * Get Employee detail
     *
     * @param Employee $employee
     * @return JsonResponse
     */
    #[Route('/api/employees/{id}', name: 'detailEmployee', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour visualiser un(e) employé(e).')]
    public function detailEmployee(Employee $employee): JsonResponse
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getEmployee')
            ->toArray();
        $jsonEmployee = $this->serializer->serialize($employee, 'json', $context);
        return new JsonResponse($jsonEmployee, Response::HTTP_OK, [], true);
    }

    /**
     * Create an Employee
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/api/employees', name: 'createEmployee', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un(e) employé(e).')]
    public function createEmployee(Request $request): JsonResponse
    {
        $employee = $this->serializer->deserialize($request->getContent(), Employee::class, 'json');
        $employee->setCreatedAt(new \DateTime());
        if($this->validatorService->checkValidation($employee)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($employee), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        $user = $employee->getUser();
        if($this->validatorService->checkValidation($user)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($user), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }
        $passwordHashed = $this->userPasswordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($passwordHashed);
        $user->setRoles(['ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Employee::class)]);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getEmployee')
            ->toArray();
        $jsonEmployee = $this->serializer->serialize($employee, 'json', $context);
        $location = $this->urlGenerator->generate('detailEmployee', ['id' => $employee->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonEmployee, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update an Employee
     *
     * @param Request $request
     * @param Employee $currentEmployee
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/api/employees/{id}', name: 'updateEmployee', methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un(e) employé(e).')]
    public function updateEmployee(Request $request, Employee $currentEmployee): JsonResponse
    {
        $currentUser = $currentEmployee->getUser();
        $currentPassword = $currentUser->getPassword();

        $updatedEmployee = $this->serializer->deserialize($request->getContent(),
            Employee::class,
            'json',
            [ AbstractNormalizer::OBJECT_TO_POPULATE => $currentEmployee, AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true]);

        if($this->validatorService->checkValidation($updatedEmployee)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($updatedEmployee), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        $updatedUser = $updatedEmployee->getUser();
        $updatedPassword = $updatedUser->getPassword();

        if($this->validatorService->checkValidation($updatedUser)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($updatedUser), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        if($currentPassword != $updatedPassword) {
            $updatedPasswordHashed = $this->userPasswordHasher->hashPassword($updatedUser, $updatedPassword);
            $updatedUser->setPassword($updatedPasswordHashed);
        }

        $this->entityManager->persist($updatedEmployee);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Employee::class)]);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getEmployee')
            ->toArray();
        $jsonEmployee = $this->serializer->serialize($updatedEmployee, 'json', $context);
        $location = $this->urlGenerator->generate('detailEmployee', ['id' => $updatedEmployee->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonEmployee, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete an Employee
     *
     * @param Employee $employee
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/api/employees/{id}', name: 'deleteEmployee', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un(e) employé(e).')]
    public function deleteEmployee(Employee $employee): JsonResponse
    {
        $this->entityManager->remove($employee);
        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Employee::class)]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
