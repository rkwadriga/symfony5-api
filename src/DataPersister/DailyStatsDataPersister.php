<?php declare(strict_types=1);
/**
 * Created 2021-10-25
 * Author Dmitry Kushneriov
 */

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\DailyStats;
use Psr\Log\LoggerInterface;

class DailyStatsDataPersister implements DataPersisterInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * @param DailyStats $data
     */
    public function persist($data): void
    {
        $this->logger->info(sprintf('Update the visitors to %s for daily stats for %s', $data->totalVisitors, $data->getDateString()));
    }

    public function remove($data): void
    {
        throw new \Exception('Not supported');
    }

    public function supports($data): bool
    {
        return $data instanceof DailyStats;
    }
}