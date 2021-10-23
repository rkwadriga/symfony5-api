<?php declare(strict_types=1);
/**
 * Created 2021-10-23
 * Author Dmitry Kushneriov
 */

namespace App\Entity;

use \DateTimeInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

#[
    ApiResource(
        collectionOperations: [
            "get"
        ],
        itemOperations: [
            /*"get" => [
                "method" => "get",
                "controller" => ApiPlatform\Core\Action\NotFoundAction::class,
                "read" => false,
                "output" => false
            ]*/
            "get"
        ],
        shortName: "daily-stats", // Also look at the "path_segment_name_generator" option in config/packages/api_platform.yaml
        normalizationContext: [
            "groups" => ["daily-stats:read"]
        ],
        paginationItemsPerPage: 7
    )
]
/**
 * @property array<CheeseListing> $mostPopularListings
 */
class DailyStats
{
    public function __construct(
        /**
         * @Groups({"daily-stats:read"})
         */
        public DateTimeInterface $date,

        /**
         * @Groups({"daily-stats:read"})
         */
        public int $totalVisitors,

        /**
         * The 5 most popular cheese listings from this date
         *
         * @var array<CheeseListing>
         *
         * @Groups({"daily-stats:read"})
         */
        public array $mostPopularListings
    ) {}

    /**
     * @ApiProperty(identifier=true)
     */
    public function getDateString(): string
    {
        return $this->date->format('Y-m-d');
    }
}