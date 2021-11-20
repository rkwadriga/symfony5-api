<?php declare(strict_types=1);
/**
 * Created 2021-11-18
 * Author Dmitry Kushneriov
 */

namespace App\Dto;

use App\Entity\CheeseListing;
use App\Entity\User;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;
use App\Validator\IsValidOwner;

class CheeseListingInput
{
    public function __construct(
        #[
            Groups(["cheese:write", "user:write"]),
            Assert\NotBlank(),
            Assert\Length([
                'min' => 5,
                'max' => 50,
                'minMessage' => 'Describe your cheese in 5 chars or more',
                'maxMessage' => 'Describe your cheese in 50 chars or less'
            ])
        ]
        public ?string $title = null,

        #[
            Groups(["cheese:write", "user:write"]),
            Assert\NotBlank()
        ]
        public ?int $price = null,

        #[
            Groups(["cheese:collection:post"]),
            IsValidOwner()
        ]
        public ?User $owner = null,

        #[
            Groups(["cheese:write"])
        ]
        public ?bool $isPublished = null,

        #[
            Assert\NotBlank()
        ]
        public ?string $description = null
    ) {}

    #[
        Groups(["cheese:write", "user:write"]),
        SerializedName("description")
    ]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    #[Pure]
    public static function createFromEntity(?CheeseListing $cheeseListing): self
    {
        if (!$cheeseListing instanceof CheeseListing) {
            return new static();
        }

        return new static(
            $cheeseListing->getTitle(),
            $cheeseListing->getPrice(),
            $cheeseListing->getOwner(),
            $cheeseListing->getIsPublished(),
            $cheeseListing->getDescription()
        );
    }

    public function createOrUpdateEntity(?CheeseListing $cheeseListing): CheeseListing
    {
        if ($cheeseListing === null) {
            $cheeseListing = new CheeseListing();
        }

        if ($this->title !== null) {
            $cheeseListing->setTitle($this->title);
        }
        if ($this->price !== null) {
            $cheeseListing->setPrice($this->price);
        }
        if ($this->description !== null) {
            $cheeseListing->setDescription($this->description);
        }
        if ($this->isPublished !== null) {
            $cheeseListing->setIsPublished($this->isPublished);
        }
        if ($this->owner !== null) {
            $cheeseListing->setOwner($this->owner);
        }

        return $cheeseListing;
    }
}