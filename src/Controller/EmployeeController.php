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

class EmployeeController extends AbstractController
{
    /**
     * Get Employee list
     *
     * @param EmployeeRepository $employeeRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/employees', name: 'listEmployee', methods: ['GET'])]
    public function listEmployee(EmployeeRepository $employeeRepository, SerializerInterface $serializer): JsonResponse
    {
        $employeeList = $employeeRepository->findAll();
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getEmployeeList')
            ->toArray();
        $jsonEmployeeList = $serializer->serialize($employeeList, 'json', $context);
        return new JsonResponse($jsonEmployeeList, Response::HTTP_OK, [], true);
    }

    /**
     * Get Employee detail
     *
     * @param Employee $employee
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/employees/{id}', name: 'detailEmployee', methods: ['GET'])]
    public function detailEmployee(Employee $employee, SerializerInterface $serializer): JsonResponse
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getEmployee')
            ->toArray();
        $jsonEmployee = $serializer->serialize($employee, 'json', $context);
        return new JsonResponse($jsonEmployee, Response::HTTP_OK, [], true);
    }

    /**
     * Create an Employee
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return JsonResponse
     */
    #[Route('/api/employees', name: 'createEmployee', methods: ['POST'])]
    public function createEmployee(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordHasherInterface $userPasswordHasher
    ): JsonResponse
    {
        $employee = $serializer->deserialize($request->getContent(), Employee::class, 'json');
        $employee->setCreatedAt(new \DateTime());

        $user = $employee->getUser();
        $passwordHashed = $userPasswordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($passwordHashed);
        $user->setRoles(['ROLE_ADMIN']);

        $entityManager->persist($user);
        $entityManager->persist($employee);
        $entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('getEmployee')
            ->toArray();
        $jsonEmployee = $serializer->serialize($employee, 'json', $context);
        $location = $urlGenerator->generate('detailEmployee', ['id' => $employee->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonEmployee, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update an Employee
     *
     * @param Request $request
     * @param Employee $currentEmployee
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return JsonResponse
     */
    #[Route('/api/employees/{id}', name: 'updateEmployee', methods: ['PUT'])]
    public function updateEmployee(
        Request $request,
        Employee $currentEmployee,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
    ): JsonResponse
    {
        $currentUser = $currentEmployee->getUser();
        $currentPassword = $currentUser->getPassword();

        $updatedEmployee = $serializer->deserialize($request->getContent(),
            Employee::class,
            'json',
            [ AbstractNormalizer::OBJECT_TO_POPULATE => $currentEmployee, AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true]);

        $updatedUser = $updatedEmployee->getUser();
        $updatedPassword = $updatedUser->getPassword();

        if($currentPassword != $updatedPassword) {
            $updatedPasswordHashed = $userPasswordHasher->hashPassword($updatedUser, $updatedPassword);
            $updatedUser->setPassword($updatedPasswordHashed);
        }

        $entityManager->persist($updatedEmployee);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete an Employee
     *
     * @param Employee $employee
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/employees/{id}', name: 'deleteEmployee', methods: ['DELETE'])]
    public function deleteEmployee(Employee $employee, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($employee);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
