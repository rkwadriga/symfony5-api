<?php declare(strict_types=1);
/**
 * Created 2021-10-23
 * Author Dmitry Kushneriov
 */

namespace App\DataProvider;

use App\Service\StatsHelper;
use IteratorAggregate;
use ArrayIterator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;

class DailyStatsPaginator implements PaginatorInterface, IteratorAggregate
{
    private ?ArrayIterator $dailyStatsIterator = null;

    public function __construct(
        private StatsHelper $statsHelper
    ) {}

    public function getLastPage(): float
    {
        return  2;
    }

    public function getTotalItems(): float
    {
        return 25;
    }

    public function getCurrentPage(): float
    {
        return 1;
    }

    public function getItemsPerPage(): float
    {
        return  10;
    }

    public function count(): float
    {
        return $this->getIterator()->count();
    }

    public function getIterator(): ArrayIterator
    {
        if ($this->dailyStatsIterator !== null) {
            return $this->dailyStatsIterator;
        }

        // @todo - actually go "load" the stats
        return $this->dailyStatsIterator = new ArrayIterator(
            $this->statsHelper->fetchMany()
        );
    }
}