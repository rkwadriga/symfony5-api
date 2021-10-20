<?php declare(strict_types=1);
/**
 * Created 2021-10-18
 * Author Dmitry Kushneriov
 */

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class UserDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private DataPersisterInterface $decoratedDataPersister,
        private PasswordHasherFactoryInterface $encoder,
        private LoggerInterface $logger
    ) {}

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
     * @return object|void
     */
    public function persist($data, array $context = []): void
    {
        if (($context['item_operation_name'] ?? null) === 'put') {
            $this->logger->info(sprintf('User %s is been updated.', $data->getEmail()));
        }

        if ($data->getId() === null) {
            $this->logger->info(sprintf('User %s just registered. Eureka!', $data->getEmail()));
        }

        if ($data->getPlainPassword() !== null) {
            $data->setPassword($this->encoder->getPasswordHasher($data)->hash($data->getPlainPassword()));
            $data->eraseCredentials();
        }

        $this->decoratedDataPersister->persist($data);
    }

    public function remove($data, array $context = []): void
    {
        $this->decoratedDataPersister->remove($data);
    }
}