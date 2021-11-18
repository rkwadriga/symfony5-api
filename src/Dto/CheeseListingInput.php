<?php declare(strict_types=1);
/**
 * Created 2021-11-18
 * Author Dmitry Kushneriov
 */

namespace App\Dto;

use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Entity\CheeseListing;
use App\Entity\User;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheeseListingInput
{
    public function __construct(
        #[Groups(["cheese:write", "user:write"])]
        public ?string $title = null,

        #[Groups(["cheese:write", "user:write"])]
        public ?int $price = null,

        #[Groups(["cheese:collection:post"])]
        public ?User $owner = null,

        #[Groups(["cheese:write"])]
        public ?bool $isPublished = null,

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