<?php declare(strict_types=1);
/**
 * Created 2021-10-25
 * Author Dmitry Kushneriov
 */

namespace App\ApiPlatform;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

class CheeseSearchFilter extends AbstractFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // TODO: Implement filterProperty() method.
    }

    public function getDescription(string $resourceClass): array
    {

    }
}