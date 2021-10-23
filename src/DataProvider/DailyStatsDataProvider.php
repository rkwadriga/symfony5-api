<?php declare(strict_types=1);
/**
 * Created 2021-10-23
 * Author Dmitry Kushneriov
 */

namespace App\DataProvider;

use App\Service\StatsHelper;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\DailyStats;

class DailyStatsDataProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private StatsHelper $statsHelper
    ) {}

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        //return $this->statsHelper->fetchMany();
        return new DailyStatsPaginator($this->statsHelper);
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        return $this->statsHelper->fetchOne($id);
    }


    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === DailyStats::class;
    }
}