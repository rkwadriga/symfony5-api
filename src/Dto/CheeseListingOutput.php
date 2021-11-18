<?php declare(strict_types=1);
/**
 * Created 2021-11-17
 * Author Dmitry Kushneriov
 */

namespace App\Dto;

use App\Entity\CheeseListing;
use App\Entity\User;
use DateTimeInterface;
use Carbon\Carbon;
use Symfony\Component\Serializer\Annotation\Groups;

class CheeseListingOutput
{
    public function __construct(
        #[Groups(["cheese:read"])]
        public int $id,

        #[Groups(["cheese:read", "user:read"])]
        public ?string $title = null,

        #[Groups(["cheese:read", "user:read"])]
        public ?int $price = null,

        #[Groups(["cheese:read"])]
        public ?User $owner = null,

        public ?bool $isPublished = null,

        #[Groups(["cheese:read"])]
        public ?string $description = null,

        public ?DateTimeInterface $createdAt = null
    ) {}

    #[Groups(["cheese:read"])]
    public function getShortDescription(): ?string
    {
        if ($this->description === null) {
            return null;
        }
        if (strlen($this->description) < 40) {
            return $this->description;
        }
        return mb_substr($this->description, 0, 40) . '...';
    }

    #[Groups(["cheese:read"])]
    public function getCreatedAtAgo(): ?string
    {
        if ($this->createdAt === null) {
            return null;
        }
        return Carbon::instance($this->createdAt)->diffForHumans();
    }

    public static function createFromEntity(CheeseListing $cheeseListing): self
    {
        return new static(
            $cheeseListing->getId(),
            $cheeseListing->getTitle(),
            $cheeseListing->getPrice(),
            $cheeseListing->getOwner(),
            $cheeseListing->getIsPublished(),
            $cheeseListing->getDescription()
        );
    }
}