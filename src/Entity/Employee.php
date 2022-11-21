<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getEmployeeList', 'getEmployee'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getEmployeeList', 'getEmployee'])]
    #[Assert\NotBlank(message: "Vous devez saisir un nom.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas contenir plus de {{ limit }} caractères.")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getEmployeeList', 'getEmployee'])]
    #[Assert\NotBlank(message: "Vous devez saisir un prénom.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le prénom doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le prénom ne peut pas contenir plus de {{ limit }} caractères.")]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getEmployee'])]
    #[Assert\NotBlank(message: "Vous devez saisir un numéro de téléphone.")]
    #[Assert\Length(
        min: 4,
        max: 50,
        minMessage: "Le numéro de téléphone doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le numéro de téléphone ne peut pas contenir plus de {{ limit }} caractères.")]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['getEmployee'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'employees', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[Groups(['getEmployee'])]
    #[Assert\NotBlank(message: "Vous devez saisir une adresse mail et un mot de passe.")]
    private ?User $user = null;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setEmployees(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getEmployees() !== $this) {
            $user->setEmployees($this);
        }

        $this->user = $user;

        return $this;
    }
}
