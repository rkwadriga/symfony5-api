<?php declare(strict_types=1);
/**
 * Created 2021-10-23
 * Author Dmitry Kushneriov
 */

namespace App\Entity;

use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use DateTime;

#[
    ApiResource(
        //shortName: "daily-stats" // look for the "path_segment_name_generator" option in config/packages/api_platform.yaml
        collectionOperations: [
            "get"
        ],
        itemOperations: [
            "get" => [
                "method" => "get",
                "controller" => NotFoundAction::class,
                "read" => false,
                "output" => false
            ]
        ]
    )
]
class DailyStats
{
    /**
     * @var DateTime|null
     */
    public ?DateTime $date = null;

    /**
     * @var int|null
     */
    public ?int $totalVisitors = null;

    /**
     * @var CheeseListing[]
     */
    public array $mostPopularListings = [];

    /**
     * @ApiProperty(identifier=true)
     */
    public function getDateString(): ?string
    {
        return $this->date !== null ? $this->date->format('Y-m-d') : null;
    }
}