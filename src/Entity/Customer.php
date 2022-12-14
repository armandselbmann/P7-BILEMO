<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "detailCustomer",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups={"getCustomerList", "getCustomer"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "createCustomer",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups = {"getCustomerList", "getCustomer"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "updateCustomer",
 *          parameters={"id"="expr(object.getId())"},
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups = {"getCustomerList", "getCustomer"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteCustomer",
 *          parameters={"id"="expr(object.getId())"},
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups = {"getCustomerList", "getCustomer"},
 *          excludeIf = "expr(not is_granted('ROLE_SUPER_ADMIN'))"
 *      )
 * )
 */
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCustomerList', 'getCustomer', 'getCustomerUserList', 'getCustomerUser'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomerList', 'getCustomer', 'getCustomerUserList', 'getCustomerUser', 'postPutCustomer'])]
    #[Assert\NotBlank(message: "Vous devez saisir un nom de soci??t??.")]
    private ?string $company = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomerList', 'getCustomer', 'postPutCustomer'])]
    #[Assert\NotBlank(message: "Vous devez saisir un nom.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom doit faire au moins {{ limit }} caract??res.",
        maxMessage: "Le nom ne peut pas contenir plus de {{ limit }} caract??res.")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomerList', 'getCustomer', 'postPutCustomer'])]
    #[Assert\NotBlank(message: "Vous devez saisir un pr??nom.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le pr??nom doit faire au moins {{ limit }} caract??res.",
        maxMessage: "Le pr??nom ne peut pas contenir plus de {{ limit }} caract??res.")]
    private ?string $firstName = null;

    #[ORM\Column(length: 10)]
    #[Groups(['getCustomer', 'postPutCustomer'])]
    #[Assert\NotBlank(message: "Vous devez saisir un code postal.")]
    #[Assert\Length(
        min: 5,
        minMessage: "Le code postal doit faire au moins {{ limit }} caract??res.")]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomer', 'postPutCustomer'])]
    #[Assert\NotBlank(message: "Vous devez saisir une adresse.")]
    private ?string $adress = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getCustomer', 'postPutCustomer'])]
    #[Assert\NotBlank(message: "Vous devez saisir une ville.")]
    #[Assert\Length(
        min: 3,
        minMessage: "La ville doit faire au moins {{ limit }} caract??res.")]
    private ?string $city = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getCustomer', 'postPutCustomer'])]
    #[Assert\NotBlank(message: "Vous devez saisir un pays.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le pays doit faire au moins {{ limit }} caract??res.")]
    private ?string $country = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getCustomerList', 'getCustomer', 'postPutCustomer'])]
    #[Assert\NotBlank(message: "Vous devez saisir un num??ro de t??l??phone.")]
    #[Assert\Length(
        min: 4,
        max: 50,
        minMessage: "Le num??ro de t??l??phone doit faire au moins {{ limit }} caract??res.",
        maxMessage: "Le num??ro de t??l??phone ne peut pas contenir plus de {{ limit }} caract??res.")]
    private ?string $phone = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getCustomer', 'postPutCustomer'])]
    private ?string $TVANumber = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getCustomer', 'postPutCustomer'])]
    private ?string $SIRET = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['getCustomer'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'customers', targetEntity: CustomerUser::class, fetch: 'EAGER', orphanRemoval: true)]
    #[Groups(['getCustomer'])]
    private Collection $customerUsers;

    #[ORM\OneToOne(mappedBy: 'customers', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[Groups(['getCustomer', 'postPutCustomer'])]
    #[Assert\NotBlank(message: "Vous devez saisir une adresse mail et un mot de passe.")]
    private ?User $user = null;

    public function __construct()
    {
        $this->customerUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
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

    public function getTVANumber(): ?string
    {
        return $this->TVANumber;
    }

    public function setTVANumber(?string $TVANumber): self
    {
        $this->TVANumber = $TVANumber;

        return $this;
    }

    public function getSIRET(): ?string
    {
        return $this->SIRET;
    }

    public function setSIRET(?string $SIRET): self
    {
        $this->SIRET = $SIRET;

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

    /**
     * @return Collection<int, CustomerUser>
     */
    public function getCustomerUsers(): Collection
    {
        return $this->customerUsers;
    }

    public function addCustomerUser(CustomerUser $customerUser): self
    {
        if (!$this->customerUsers->contains($customerUser)) {
            $this->customerUsers->add($customerUser);
            $customerUser->setCustomers($this);
        }

        return $this;
    }

    public function removeCustomerUser(CustomerUser $customerUser): self
    {
        if ($this->customerUsers->removeElement($customerUser)) {
            // set the owning side to null (unless already changed)
            if ($customerUser->getCustomers() === $this) {
                $customerUser->setCustomers(null);
            }
        }

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
            $this->user->setCustomers(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getCustomers() !== $this) {
            $user->setCustomers($this);
        }

        $this->user = $user;

        return $this;
    }
}
