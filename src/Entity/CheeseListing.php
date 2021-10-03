<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use \DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ApiResource(
        collectionOperations: [
            "get" => ["path" => "/cheeses/list.{_format}"], // /cheeses/list.json, /cheeses/list.jsonld
            "post"
        ],
        itemOperations: [
            "get" => [
                "normalization_context" => [
                    "groups" => [
                        "cheese_listing:read",
                        "cheese_listing:item:get"
                    ]
                ]
            ],
            "put"
        ],
        shortName: "cheeses",
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
        denormalizationContext: [
            "groups" => [
                "cheese_listing:write"
            ],
            "swagger_definition_name" => "Write"
        ],
        normalizationContext: [
            "groups" => [
                "cheese_listing:read"
            ],
            "swagger_definition_name" => "Read"
        ]
    ),
    ApiFilter(BooleanFilter::class, properties: ["isPublished"]),
    ApiFilter(SearchFilter::class, properties: [
        "title" => "partial",
        "description" => "partial",
        "owner" => "exact", // Needed for filtering the collection by owner (add to the uri something like "?owner=/api/users/<user ID>")
        "owner.username" => "partial" // Needed for filtering the collection by owner.username (add to the uri something like "?owner.username=<part of the owner's username>")
    ]),
    ApiFilter(RangeFilter::class, properties: ["price"]),
    ApiFilter(PropertyFilter::class) // Make possible send response like "GET /cheeses-list?properties[]=title&properties[]=price"
]
/**
 * @ORM\Entity (repositoryClass=CheeseListingRepository::class)
 */
class CheeseListing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"cheese_listing:read", "cheese_listing:write", "user:read", "user:write"})
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
     * @Groups({"cheese_listing:read"})
     * @Assert\NotBlank()
     */
    private string $description;

    /**
     * The price of this delicious cheese, in cents
     *
     * @ORM\Column(type="integer")
     * @Groups({"cheese_listing:read", "cheese_listing:write", "user:read", "user:write"})
     * @Assert\NotBlank()
     */
    private int $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isPublished = false;

    /**
     * @Assert\Valid()
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"cheese_listing:read", "cheese_listing:write"})
     */
    private User $owner;

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * The description of the cheese as raw text
     *
     * @Groups({"cheese_listing:write", "user:write"})
     * @SerializedName("description")
     */
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    /**
     * @Groups({"cheese_listing:read"})
     */
    public function getShortDescription(): ?string
    {
        if (strlen($this->getDescription()) < 40) {
            return $this->getDescription();
        }
        return mb_substr($this->getDescription(), 0, 40) . '...';
    }

    public function getPrice(): int
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

    /**
     * How long ago in text that this cheese listing was added.
     *
     * @Groups({"cheese_listing:read"})
     */
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
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

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
