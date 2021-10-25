<?php declare(strict_types=1);
/**
 * Created 2021-10-25
 * Author Dmitry Kushneriov
 */

namespace App\ApiPlatform;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

class CheeseSearchFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property !== 'search') {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        // A param name that is generated unique in this query
        $valueParameter = $queryNameGenerator->generateParameterName('serach');
        $queryBuilder
            ->andWhere("{$alias}.title LIKE :{$valueParameter} OR {$alias}.description LIKE :{$valueParameter}")
            ->setParameter($valueParameter, "%{$value}%")
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return ['search' => [
            'property' => null,
            'type' => 'string',
            'required' => false,
            'description' => 'Search across multiple fields',
        ]];
    }
}