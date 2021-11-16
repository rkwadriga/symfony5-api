<?php declare(strict_types=1);
/**
 * Created 2021-10-23
 * Author Dmitry Kushneriov
 */

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\Pagination;
use App\Service\StatsHelper;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\DailyStats;

class DailyStatsDataProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private StatsHelper $statsHelper,
        private Pagination $pagination
    ) {}

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        [$page, $offset, $limit] = $this->pagination->getPagination($resourceClass, $operationName);

        return new DailyStatsPaginator($this->statsHelper, $page, $limit, new \DateTime('2021-10-20'));
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