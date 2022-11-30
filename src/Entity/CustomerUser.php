<?php

namespace App\Entity;

use App\Repository\CustomerUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "detailCustomerUser",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"getCustomerUserList", "getCustomerUser"})
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "createCustomerUser",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"getCustomerUserList", "getCustomerUser"})
 * )
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "updateCustomerUser",
 *          parameters={"id"="expr(object.getId())"},
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"getCustomerUserList", "getCustomerUser"})
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteCustomerUser",
 *          parameters={"id"="expr(object.getId())"},
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"getCustomerUserList", "getCustomerUser"})
 * )
 */
#[ORM\Entity(repositoryClass: CustomerUserRepository::class)]
class CustomerUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCustomerUserList', 'getCustomerUser', 'getCustomer'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomerUserList', 'getCustomerUser', 'getCustomer', 'postPutCustomerUser'])]
    #[Assert\NotBlank(message: "Vous devez saisir un nom.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas contenir plus de {{ limit }} caractères.")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomerUser', 'postPutCustomerUser'])]
    #[Assert\NotBlank(message: "Vous devez saisir un prénom.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le prénom doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le prénom ne peut pas contenir plus de {{ limit }} caractères.")]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getCustomerUserList', 'getCustomerUser', 'getCustomer', 'postPutCustomerUser'])]
    #[Assert\Email(message: 'Cet email {{ value }} n\'est pas valide')]
    private ?string $email = null;

    #[ORM\Column(length: 10)]
    #[Groups(['getCustomerUser', 'postPutCustomerUser'])]
    #[Assert\NotBlank(message: "Vous devez saisir un code postal.")]
    #[Assert\Length(
        min: 5,
        minMessage: "Le code postal doit faire au moins {{ limit }} caractères.")]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomerUser', 'postPutCustomerUser'])]
    #[Assert\NotBlank(message: "Vous devez saisir une adresse.")]
    private ?string $adress = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getCustomerUser', 'postPutCustomerUser'])]
    #[Assert\NotBlank(message: "Vous devez saisir une ville.")]
    #[Assert\Length(
        min: 3,
        minMessage: "La ville doit faire au moins {{ limit }} caractères.")]
    private ?string $city = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getCustomerUser', 'postPutCustomerUser'])]
    #[Assert\NotBlank(message: "Vous devez saisir un pays.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le pays doit faire au moins {{ limit }} caractères.")]
    private ?string $country = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getCustomerUser', 'postPutCustomerUser'])]
    #[Assert\NotBlank(message: "Vous devez saisir un numéro de téléphone.")]
    #[Assert\Length(
        min: 4,
        max: 50,
        minMessage: "Le numéro de téléphone doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le numéro de téléphone ne peut pas contenir plus de {{ limit }} caractères.")]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['getCustomerUser'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'customerUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getCustomerUserList', 'getCustomerUser'])]
    private ?Customer $customers = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCustomers(): ?Customer
    {
        return $this->customers;
    }

    public function setCustomers(?Customer $customers): self
    {
        $this->customers = $customers;

        return $this;
    }
}
