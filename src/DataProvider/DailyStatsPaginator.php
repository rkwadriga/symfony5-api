<?php declare(strict_types=1);
/**
 * Created 2021-10-23
 * Author Dmitry Kushneriov
 */

namespace App\DataProvider;

use DateTimeInterface;
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
        private int $maxResults,
        private ?DateTimeInterface $fromDate = null,
    ) {}

    public function getLastPage(): float
    {
        return ceil($this->getTotalItems() / $this->getItemsPerPage()) ?: 1;
    }

    public function getTotalItems(): float
    {
        return $this->statsHelper->count($this->getCriteria());
    }

    public function getCurrentPage(): float
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): float
    {
        return  $this->maxResults;
    }

    public function setFromDate(DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
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
            $this->statsHelper->fetchMany((int)$this->getItemsPerPage(), (int)$offset, $this->getCriteria())
        );
    }

    private function getCriteria(): array
    {
        $criteria = [];
        if ($this->fromDate !== null) {
            $criteria['from'] = $this->fromDate;
        }

        return $criteria;
    }
}