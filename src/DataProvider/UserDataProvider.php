<?php declare(strict_types=1);
/**
 * Created 2021-10-21
 * Author Dmitry Kushneriov
 */

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class UserDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private CollectionDataProviderInterface $collectionDataProvider,
        private Security $security
    ) {}

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        /** @var User[] $users */
        // In this case our collectionDataProvider is ApiPlatform\Core\Bridge\Doctrine\Orm\CollectionDataProvider
        $users = $this->collectionDataProvider->getCollection($resourceClass, $operationName, $context);

        $currentUser = $this->security->getUser();
        foreach ($users as $user) {
            $user->setIsMe($user === $currentUser);
        }

        return $users;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === User::class;
    }

}