<?php declare(strict_types=1);
/**
 * Created 2021-10-18
 * Author Dmitry Kushneriov
 */

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class UserDataPersister implements DataPersisterInterface
{
    public function __construct(
        private DataPersisterInterface $decoratedDataPersister,
        private PasswordHasherFactoryInterface $encoder
    ) {}

    public function supports($data): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
     * @return object|void
     */
    public function persist($data)
    {
        if ($data->getPlainPassword() !== null) {
            $data->setPassword($this->encoder->getPasswordHasher($data)->hash($data->getPlainPassword()));
            $data->eraseCredentials();
        }

        $this->decoratedDataPersister->persist($data);
    }

    public function remove($data)
    {
        $this->decoratedDataPersister->remove($data);
    }
}