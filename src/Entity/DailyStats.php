<?php declare(strict_types=1);
/**
 * Created 2021-10-23
 * Author Dmitry Kushneriov
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use DateTime;

#[
    ApiResource(
        //shortName: "daily-stats" // look for the "path_segment_name_generator" option in config/packages/api_platform.yaml
    )
]
class DailyStats
{
    /**
     * @ApiProperty(identifier=true)
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
}