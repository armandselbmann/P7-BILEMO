<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
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
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordHasherInterface $userPasswordHasher
    )
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * Get Employee list
     *
     * @param EmployeeRepository $employeeRepository
     * @return JsonResponse
     */
    #[Route('/api/employees', name: 'listEmployee', methods: ['GET'])]
    public function listEmployee(EmployeeRepository $employeeRepository): JsonResponse
    {
        $employeeList = $employeeRepository->findAll();
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
     */
    #[Route('/api/employees', name: 'createEmployee', methods: ['POST'])]
    public function createEmployee(Request $request): JsonResponse
    {
        $employee = $this->serializer->deserialize($request->getContent(), Employee::class, 'json');
        $employee->setCreatedAt(new \DateTime());

        $user = $employee->getUser();
        $passwordHashed = $this->userPasswordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($passwordHashed);
        $user->setRoles(['ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();

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
     */
    #[Route('/api/employees/{id}', name: 'updateEmployee', methods: ['PUT'])]
    public function updateEmployee(Request $request, Employee $currentEmployee): JsonResponse
    {
        $currentUser = $currentEmployee->getUser();
        $currentPassword = $currentUser->getPassword();

        $updatedEmployee = $this->serializer->deserialize($request->getContent(),
            Employee::class,
            'json',
            [ AbstractNormalizer::OBJECT_TO_POPULATE => $currentEmployee, AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true]);

        $updatedUser = $updatedEmployee->getUser();
        $updatedPassword = $updatedUser->getPassword();

        if($currentPassword != $updatedPassword) {
            $updatedPasswordHashed = $this->userPasswordHasher->hashPassword($updatedUser, $updatedPassword);
            $updatedUser->setPassword($updatedPasswordHashed);
        }

        $this->entityManager->persist($updatedEmployee);
        $this->entityManager->flush();

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
     */
    #[Route('/api/employees/{id}', name: 'deleteEmployee', methods: ['DELETE'])]
    public function deleteEmployee(Employee $employee): JsonResponse
    {
        $this->entityManager->remove($employee);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
