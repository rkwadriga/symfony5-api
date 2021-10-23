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
        private StatsHelper $statsHelper,
        private int $currentPage,
        private int $maxResults
    ) {}

    public function getLastPage(): float
    {
        return ceil($this->getTotalItems() / $this->getItemsPerPage()) ?: 1;
    }

    public function getTotalItems(): float
    {
        return $this->statsHelper->count();
    }

    public function getCurrentPage(): float
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): float
    {
        return  $this->maxResults;
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

        $offset = ($this->getCurrentPage() - 1) * $this->getItemsPerPage();

        return $this->dailyStatsIterator = new ArrayIterator(
            $this->statsHelper->fetchMany((int)$this->getItemsPerPage(), (int)$offset)
        );
    }
}