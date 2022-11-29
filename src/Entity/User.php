<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email', message: "Veuillez saisir une adresse mail différente. Celle-ci correspond déjà à un utilisateur.")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['getCustomer', 'getEmployee', 'postPutCustomer', 'postPutEmployee'])]
    #[Assert\NotBlank(message: "Vous devez saisir une adresse mail.")]
    #[Assert\Email(message: 'Cet email {{ value }} n\'est pas valide')]
    private ?string $email = null;

    /**
     * @var array
     */
    #[ORM\Column]
    #[Groups(['getCustomer', 'getEmployee'])]
    private array $roles = [];

    /**
     * @var string|null
     */
    #[ORM\Column]
    #[Groups(['postPutCustomer', 'postPutEmployee'])]
    #[Assert\NotBlank(message: "Vous devez saisir un mot de passe.")]
    #[Assert\Length(
        min: 4,
        minMessage: "Le mot de passe doit faire au moins {{ limit }} caractères.")]
    private ?string $password = null;

    /**
     * @var Customer|null
     */
    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Customer $customers = null;

    /**
     * @var Employee|null
     */
    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Employee $employees = null;

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getEmployees(): ?Employee
    {
        return $this->employees;
    }

    public function setEmployees(?Employee $employees): self
    {
        $this->employees = $employees;

        return $this;
    }
}
