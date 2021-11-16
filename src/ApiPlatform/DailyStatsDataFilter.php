<?php declare(strict_types=1);
/**
 * Created 2021-11-16
 * Author Dmitry Kushneriov
 */

namespace App\ApiPlatform;

use ApiPlatform\Core\Serializer\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;

class DailyStatsDataFilter implements FilterInterface
{
    public function apply(Request $request, bool $normalization, array $attributes, array &$context)
    {
        // TODO: Implement apply() method.
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