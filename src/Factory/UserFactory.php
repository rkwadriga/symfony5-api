<?php

namespace App\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\SecurityHelper;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static User|Proxy findOrCreate(array $attributes)
 * @method static User|Proxy random()
 * @method static User[]|Proxy[] randomSet(int $number)
 * @method static User[]|Proxy[] randomRange(int $min, int $max)
 * @method static UserRepository|RepositoryProxy repository()
 * @method User|Proxy create($attributes = [])
 * @method User[]|Proxy[] createMany(int $number, $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    const DEFAULT_PASSWORD = 'test';
    const DEFAULT_PHONE_NUMBER = '(000) 111-222-333';

    public function admin(): self
    {
        return $this->addState(['roles' => [SecurityHelper::ROLE_ADMIN]]);
    }

    public function withPhoneNumber(string $number = self::DEFAULT_PHONE_NUMBER): self
    {
        return $this->addState(['phoneNumber' => $number]);
    }

    protected function getDefaults(): array
    {
        return [
            'email' => self::faker()->email(),
            'username' => self::faker()->userName(),
            // hashed version of "test"
            // php bin/console security:encode-password --env=test
            'password' => '$argon2id$v=19$m=10,t=3,p=1$eyXPWiQFWUO901E78Bb3UQ$hyu9dFDz7fo2opQyCSoX/NfJDvEpzER/a+WbiAagqqw',
        ];
    }

    protected function initialize(): self
    {
        // see https://github.com/zenstruck/foundry#initialization
        return $this
            // ->beforeInstantiate(function(User $user) {})
        ;
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
