<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Dto\CheeseListingInput;
use App\Dto\CheeseListingOutput;
use App\Repository\CheeseListingRepository;
use App\Validator\IsValidOwner;
use Doctrine\ORM\Mapping as ORM;
use \DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\ValidIsPublished;
use App\ApiPlatform\CheeseSearchFilter;

#[
    ApiResource(
        collectionOperations: [
            "get" => ["path" => "/cheeses/list.{_format}"], // /cheeses/list.json, /cheeses/list.jsonld
            "post" => [
                "security" => "is_granted('ROLE_USER')"
            ]
        ],
        itemOperations: [
            "get" => [
                "normalization_context" => [
                    "groups" => [
                        "cheese:read",
                        "cheese:item:get"
                    ]
                ]
            ],
            "put" => [
                //"security" => "is_granted('ROLE_USER') and object.getOwner() === user",
                "security" => "is_granted('EDIT', object)", // The same like previous string but logics is implemented in App\Security\Voter\CheeseListingVoter
                "security_message" => "Only the owner can edit the cheese listing"
            ],
            "delete" => [
                "security" => "is_granted('ROLE_ADMIN')"
            ]
        ],
        shortName: "cheese",
        attributes: [
            "pagination_items_per_page" => 10,
            "formats" => [
                "json",
                "jsonld",
                "html",
                "jsonhal",
                "csv" => ["text/csv"]
            ]
        ],
        input: CheeseListingInput::class,
        output: CheeseListingOutput::class
        /*denormalizationContext: [
            "groups" => [
                "cheese:write"
            ],
            "swagger_definition_name" => "Write"
        ],
        normalizationContext: [
            "groups" => [
                "cheese:read"
            ],
            "swagger_definition_name" => "Read"
        ]*/
    ),
    ApiFilter(BooleanFilter::class, properties: ["isPublished"]),
    ApiFilter(SearchFilter::class, properties: [
        "title" => "partial",
        "description" => "partial",
        "owner" => "exact", // Needed for filtering the collection by owner (add to the uri something like "?owner=/api/users/<user ID>")
        "owner.username" => "partial" // Needed for filtering the collection by owner.username (add to the uri something like "?owner.username=<part of the owner's username>")
    ]),
    ApiFilter(CheeseSearchFilter::class),
    ApiFilter(RangeFilter::class, properties: ["price"]),
    ApiFilter(PropertyFilter::class), // Make possible send response like "GET /cheeses-list?properties[]=title&properties[]=price"
    ValidIsPublished(['descriptionMinLength' => 100])
]
/**
 * @ORM\Entity (repositoryClass=CheeseListingRepository::class)
 * @ORM\EntityListeners({"App\Doctrine\CheeseListingSetOwnerListener"})
 */
class CheeseListing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"cheese:read"})
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * //Groups({"cheese:write", "user:write"}) // Look at the App\Dto\CheeseListingInput class
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     maxMessage="Describe your cheese in 50 chars or less"
     * )
     */
    private ?string $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private ?string $description = null;

    /**
     * The price of this delicious cheese, in cents
     *
     * @ORM\Column(type="integer")
     * //Groups({"cheese:write", "user:write"})
     * @Assert\NotBlank()
     */
    private ?int $price = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="boolean")
     * //Groups({"cheese:write"})
     */
    private bool $isPublished = false;

    /**
     * //Assert\Valid()
     * @IsValidOwner()
     * //Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     * //Groups({"cheese:read", "cheese:write"})
     * //Groups({"cheese:collection:post"}) // See the dynamic added groups in App\ApiPlatform\AutoGroupResourceMetadataFactory.getDefaultGroups()
     */
    private ?User $owner = null;

    public function __construct(string $title = null)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->title = $title;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
