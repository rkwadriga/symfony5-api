<?php declare(strict_types=1);
/**
 * Created 2021-11-16
 * Author Dmitry Kushneriov
 */

namespace App\ApiPlatform;

use DateTimeImmutable;
use ApiPlatform\Core\Serializer\Filter\FilterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DailyStatsDataFilter implements FilterInterface
{
    public const FROM_FILTER_CONTEXT = 'daily_stats_from';

    public function __construct(
        private LoggerInterface $logger,
        private bool $throwOnInvalid = false
    ) {}

    public function apply(Request $request, bool $normalization, array $attributes, array &$context)
    {
        $from = $request->query->get('from');
        if (!$from) {
            return;
        }

        $fromDate = DateTimeImmutable::createFromFormat('Y-m-d', $from);
        if (!$fromDate && $this->throwOnInvalid) {
            throw new BadRequestHttpException('Invalid "from" date format');
        }

        if ($fromDate) {
            $this->logger->info(sprintf('Filtering from date "%s"', $from));
            $fromDate = $fromDate->setTime(0, 0, 0);
            $context[self::FROM_FILTER_CONTEXT] = $fromDate;
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return ['from' => [
            'property' => null,
            'type' => 'string',
            'required' => false,
            'description' => 'From date e.g. 2021-09-05',
        ]];
    }
}