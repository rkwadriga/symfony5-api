<?php declare(strict_types=1);
/**
 * Created 2021-11-17
 * Author Dmitry Kushneriov
 */

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class CheeseListingOutput
{
    public function __construct(
        /**
         * @Groups({"cheese:read"})
         */
        public ?string $title = null
    ) {}
}