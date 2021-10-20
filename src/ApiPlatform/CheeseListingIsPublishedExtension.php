<?php declare(strict_types=1);
/**
 * Created 2021-10-17
 * Author Dmitry Kushneriov
 */

namespace App\ApiPlatform;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\CheeseListing;
use App\Security\SecurityHelper;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class CheeseListingIsPublishedExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private Security $security
    ) {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    private function addWhere(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== CheeseListing::class || $this->security->isGranted(SecurityHelper::ROLE_ADMIN)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if (!$this->security->getUser()) {
            $queryBuilder
                ->andWhere(sprintf('%s.isPublished = :isPublished', $rootAlias))
                ->setParameter('isPublished', true);
        } else {
            $queryBuilder
                ->andWhere(sprintf('
                        %s.isPublished = :isPublished
                        OR %s.owner = :owner',
                    $rootAlias, $rootAlias
                ))
                ->setParameter('isPublished', true)
                ->setParameter('owner', $this->security->getUser());
        }
    }
}