<?php declare(strict_types=1);
/**
 * Created 2021-11-18
 * Author Dmitry Kushneriov
 */

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheeseListingInput
{
    public function __construct(
        #[Groups(["cheese:write", "user:write"])]
        public string $title,

        #[Groups(["cheese:write", "user:write"])]
        public int $price,

        #[Groups(["cheese:collection:post"])]
        public User $owner,

        #[Groups(["cheese:write"])]
        public bool $isPublished = true,

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
}