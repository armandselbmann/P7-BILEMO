<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
use OpenApi\Attributes as OA;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "detailProduct",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"getProductList", "getProduct"})
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "createProduct",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups = {"getProductList", "getProduct"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "updateProduct",
 *          parameters = {"id"="expr(object.getId())"},
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups = {"getProductList", "getProduct"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteProduct",
 *          parameters = {"id"="expr(object.getId())"},
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups = {"getProductList", "getProduct"},
 *          excludeIf = "expr(not is_granted('ROLE_SUPER_ADMIN'))"
 *      )
 * )
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[UniqueEntity('reference', message: "Veuillez saisir une référence différente. Celle-ci correspond déjà à un produit.")]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getProductList', 'getProduct', 'getImage'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getProductList', 'getProduct', 'postPutProduct', 'getImage'])]
    #[OA\Property(description: 'Smartphone reference. It is unique and does not match any other smartphone.')]
    #[Assert\NotBlank(message: "Vous devez saisir une référence.")]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[OA\Property(description: 'Release date of the product.')]
    #[Groups(['getProductList', 'getProduct'])]
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getProductList', 'getProduct', 'postPutProduct'])]
    #[OA\Property(description: 'Serial number.')]
    #[Assert\NotBlank(message: "Vous devez saisir un numéro de série.")]
    private ?string $series = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getProductList', 'getProduct', 'postPutProduct'])]
    #[OA\Property(description: 'Smartphone name.')]
    #[Assert\NotBlank(message: "Vous devez saisir un nom.")]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['getProductList', 'getProduct', 'postPutProduct'])]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getProductList', 'getProduct', 'postPutProduct'])]
    #[OA\Property(description: 'Smartphone maker.')]
    #[Assert\NotBlank(message: "Vous devez saisir un fabricant.")]
    private ?string $maker = null;

    #[ORM\Column]
    #[Groups(['getProductList', 'getProduct', 'postPutProduct'])]
    #[OA\Property(description: 'Smartphone price.')]
    #[Assert\NotBlank(message: "Vous devez saisir un prix.")]
    private ?int $price = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getProduct', 'postPutProduct'])]
    #[OA\Property(description: 'Smartphone color.')]
    #[Assert\NotBlank(message: "Vous devez saisir une couleur.")]
    private ?string $color = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getProduct', 'postPutProduct'])]
    #[OA\Property(description: 'Smartphone operating systeme.')]
    #[Assert\NotBlank(message: "Vous devez saisir le système d'exploitation.")]
    private ?string $platform = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $network = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $connector = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $battery = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $RAM = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $ROM = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $brandCPU = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $speedCPU = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?int $coresCPU = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $mainCAM = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $subCam = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $displayType = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?string $displaySize = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?bool $doubleSIM = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?bool $cardReader = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?bool $foldable = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?bool $eSIM = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?int $width = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?int $height = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?int $depth = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getProduct', 'postPutProduct'])]
    private ?int $weight = null;

    #[ORM\OneToMany(mappedBy: 'products', targetEntity: Image::class, fetch: 'EAGER', orphanRemoval: true)]
    #[Groups(['getProduct'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getSeries(): ?string
    {
        return $this->series;
    }

    public function setSeries(string $series): self
    {
        $this->series = $series;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMaker(): ?string
    {
        return $this->maker;
    }

    public function setMaker(string $maker): self
    {
        $this->maker = $maker;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getNetwork(): ?string
    {
        return $this->network;
    }

    public function setNetwork(?string $network): self
    {
        $this->network = $network;

        return $this;
    }

    public function getConnector(): ?string
    {
        return $this->connector;
    }

    public function setConnector(?string $connector): self
    {
        $this->connector = $connector;

        return $this;
    }

    public function getBattery(): ?string
    {
        return $this->battery;
    }

    public function setBattery(?string $battery): self
    {
        $this->battery = $battery;

        return $this;
    }

    public function getRAM(): ?string
    {
        return $this->RAM;
    }

    public function setRAM(?string $RAM): self
    {
        $this->RAM = $RAM;

        return $this;
    }

    public function getROM(): ?string
    {
        return $this->ROM;
    }

    public function setROM(?string $ROM): self
    {
        $this->ROM = $ROM;

        return $this;
    }

    public function getBrandCPU(): ?string
    {
        return $this->brandCPU;
    }

    public function setBrandCPU(?string $brandCPU): self
    {
        $this->brandCPU = $brandCPU;

        return $this;
    }

    public function getSpeedCPU(): ?string
    {
        return $this->speedCPU;
    }

    public function setSpeedCPU(?string $speedCPU): self
    {
        $this->speedCPU = $speedCPU;

        return $this;
    }

    public function getCoresCPU(): ?int
    {
        return $this->coresCPU;
    }

    public function setCoresCPU(?int $coresCPU): self
    {
        $this->coresCPU = $coresCPU;

        return $this;
    }

    public function getMainCAM(): ?string
    {
        return $this->mainCAM;
    }

    public function setMainCAM(?string $mainCAM): self
    {
        $this->mainCAM = $mainCAM;

        return $this;
    }

    public function getSubCam(): ?string
    {
        return $this->subCam;
    }

    public function setSubCam(?string $subCam): self
    {
        $this->subCam = $subCam;

        return $this;
    }

    public function getDisplayType(): ?string
    {
        return $this->displayType;
    }

    public function setDisplayType(?string $displayType): self
    {
        $this->displayType = $displayType;

        return $this;
    }

    public function getDisplaySize(): ?string
    {
        return $this->displaySize;
    }

    public function setDisplaySize(?string $displaySize): self
    {
        $this->displaySize = $displaySize;

        return $this;
    }

    public function isDoubleSIM(): ?bool
    {
        return $this->doubleSIM;
    }

    public function setDoubleSIM(?bool $doubleSIM): self
    {
        $this->doubleSIM = $doubleSIM;

        return $this;
    }

    public function isCardReader(): ?bool
    {
        return $this->cardReader;
    }

    public function setCardReader(?bool $cardReader): self
    {
        $this->cardReader = $cardReader;

        return $this;
    }

    public function isFoldable(): ?bool
    {
        return $this->foldable;
    }

    public function setFoldable(?bool $foldable): self
    {
        $this->foldable = $foldable;

        return $this;
    }

    public function isESIM(): ?bool
    {
        return $this->eSIM;
    }

    public function setESIM(?bool $eSIM): self
    {
        $this->eSIM = $eSIM;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getDepth(): ?int
    {
        return $this->depth;
    }

    public function setDepth(?int $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProducts($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProducts() === $this) {
                $image->setProducts(null);
            }
        }

        return $this;
    }
}
