<?php

namespace App\Controller;

use App\Entity\Employee;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Employee Controller methods
 */
class EmployeeController extends AbstractController
{
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
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var ExistingObjectConstructor
     */
    private ExistingObjectConstructor $objectConstructor;

    /**
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param ValidatorService $validatorService
     * @param PaginationService $paginationService
     * @param TagAwareCacheInterface $cachePool
     * @param SerializerInterface $serializer
     * @param ExistingObjectConstructor $objectConstructor
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorService $validatorService,
        PaginationService $paginationService,
        TagAwareCacheInterface $cachePool,
        SerializerInterface $serializer,
        ExistingObjectConstructor $objectConstructor
    )
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->validatorService = $validatorService;
        $this->paginationService = $paginationService;
        $this->cachePool = $cachePool;
        $this->serializer = $serializer;
        $this->objectConstructor = $objectConstructor;
    }

    /**
     * List all the BileMo employees --- ACCESS RESTRICTED TO ADMINISTRATORS ---
     *
     * @param Request $request
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException|InvalidArgumentException
     *
     * @OA\Get(
     *      description="List all the BileMo employees. Access restricted to administrators.",
     *      tags = {"Employees"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns employee list",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Employee_light"))
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
    #[Route('/api/employees', name: 'listEmployee', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour visualiser la liste des employé(e)s.')]
    public function listEmployee(Request $request): JsonResponse
    {
        $employeeList = $this->paginationService->paginationList($request, Employee::class);
        $context = SerializationContext::create()->setGroups(['getEmployeeList']);
        $jsonEmployeeList = $this->serializer->serialize($employeeList, 'json', $context);
        return new JsonResponse($jsonEmployeeList, Response::HTTP_OK, [], true);
    }

    /**
     * List the characteristics of the specified employee --- ACCESS RESTRICTED TO ADMINISTRATORS ---
     *
     * @param Employee $employee
     * @return JsonResponse
     *
     * @OA\Get(
     *     description="List the characteristics of the specified employee. Access restricted to administrators.",
     *     tags = {"Employees"},
     *     @OA\Response(
     *          response=200,
     *          description="Returns employee detail",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Employee"))
     *          )
     *      ),
     *      @OA\Response(response=400, description="Bad Request: This method is not allowed for this route"),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response=403, description="Forbidden: access denied"),
     *      @OA\Response(response=404, description="Object not found: Invalid route or resource ID")
     * )
     */
    #[Route('/api/employees/{id}', name: 'detailEmployee', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour visualiser un(e) employé(e).')]
    public function detailEmployee(Employee $employee): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getEmployee']);
        $jsonEmployee = $this->serializer->serialize($employee, 'json', $context);
        return new JsonResponse($jsonEmployee, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new employee --- ACCESS RESTRICTED TO ADMINISTRATORS ---
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Post(
     *     description="Create a new employee. Access restricted to administrators.",
     *     tags = {"Employees"},
     *     @OA\RequestBody(
     *          description="Employee that needs to be added.",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Employee_post_put"),
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Created - Returns employee details",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Employee"))
     *          )
     *      ),
     *      @OA\Response(response="400", description="Bad Request: Invalid content"),
     *      @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *      @OA\Response(response="403", description="Forbidden: You are not allowed to access to this page"),
     * )
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
        $user->setEmployees($employee);

        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Employee::class)]);

        $context = SerializationContext::create()->setGroups(['getEmployee']);
        $jsonEmployee = $this->serializer->serialize($employee, 'json', $context);
        $location = $this->urlGenerator->generate('detailEmployee', ['id' => $employee->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonEmployee, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Update an Employee --- ACCESS RESTRICTED TO ADMINISTRATORS ---
     *
     * @param Request $request
     * @param Employee $currentEmployee
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Put(
     *     description="Update a Employee. This operation does not allow to modify the images linked to a product. Access restricted to administrators.",
     *     tags = {"Employees"},
     *     @OA\RequestBody(
     *          description="Properties of an employee that can be update.",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Employee_post_put"),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation - Returns the updated employee",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Employee"))
     *          )
     *      ),
     * @OA\Response(response="400", description="Bad Request: This method is not allowed for this route OR Could not decode JSON, syntax error - malformed JSON. OR The JSON sent contains invalid data."),
     * @OA\Response(response=401, description="Unauthorized: Expired JWT Token/JWT Token not found"),
     * @OA\Response(response="403", description="Forbidden: You are not allowed to access to this page"),
     * )
     */
    #[Route('/api/employees/{id}', name: 'updateEmployee', methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un(e) employé(e).')]
    public function updateEmployee(Request $request, Employee $currentEmployee): JsonResponse
    {
        $currentUser = $currentEmployee->getUser();
        $currentPassword = $currentUser->getPassword();

        $newEmployee = $this->serializer->deserialize($request->getContent(), Employee::class, 'json');
        $currentEmployee = $this->objectConstructor->employeeConstructor($newEmployee, $currentEmployee);

        if($this->validatorService->checkValidation($currentEmployee)) {
            return new JsonResponse(
                $this->serializer->serialize($this->validatorService->checkValidation($currentEmployee), 'json'),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        if ($newEmployee->getUser()){
            if ($newEmployee->getUser()->getEmail()) {
                $currentUser->setEmail($newEmployee->getUser()->getEmail());
            }
            if ($newEmployee->getUser()->getPassword()) {
                $newPassword = $newEmployee->getUser()->getPassword();
                if($this->validatorService->checkValidation($currentUser)) {
                    return new JsonResponse(
                        $this->serializer->serialize($this->validatorService->checkValidation($currentUser), 'json'),
                        Response::HTTP_BAD_REQUEST, [], true);
                }
                if($currentPassword != $newPassword) {
                    $updatedPasswordHashed = $this->userPasswordHasher->hashPassword($currentUser, $newPassword);
                    $currentUser->setPassword($updatedPasswordHashed);
                }
            }
        }

        $this->entityManager->flush();

        $this->cachePool->invalidateTags([stripslashes(Employee::class)]);

        $context = SerializationContext::create()->setGroups(['getEmployee']);
        $jsonEmployee = $this->serializer->serialize($currentEmployee, 'json', $context);
        $location = $this->urlGenerator->generate('detailEmployee', ['id' => $currentEmployee->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonEmployee, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Delete an Employee --- ACCESS RESTRICTED TO ADMINISTRATORS ---
     *
     * @param Employee $employee
     * @return JsonResponse
     * @throws InvalidArgumentException
     *
     * @OA\Delete(
     *     description="Delete the specified employee. Access restricted to administrators.",
     *     tags = {"Employees"},
     *     @OA\Response(response=204, description="Successful operation: No-Content"),
     *     @OA\Response(response="400", description="Bad Request: This method is not allowed for this route"),
     *     @OA\Response(response="401", description="Unauthorized: Expired JWT Token/JWT Token not found"),
     *     @OA\Response(response="403", description="Forbidden: You are not allowed to access to this page"),
     *     @OA\Response(response="404", description="Object not found: Invalid route or resource ID")
     * )
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
