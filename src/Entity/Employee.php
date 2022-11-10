<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getEmployeeList', 'getEmployee'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getEmployee'])]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['getEmployee'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'employees', cascade: ['persist', 'remove'])]
    #[Groups(['getEmployee'])]
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
