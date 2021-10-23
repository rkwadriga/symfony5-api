<?php declare(strict_types=1);
/**
 * Created 2021-10-23
 * Author Dmitry Kushneriov
 */

namespace App\DataProvider;

use DateTime;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\DailyStats;

class DailyStatsDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $stats = new DailyStats();
        $stats->date = new DateTime();
        $stats->totalVisitors = 100;

        return [$stats];
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === DailyStats::class;
    }
}