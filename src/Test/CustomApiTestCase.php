<?php declare(strict_types=1);
/**
 * Created 2021-10-08
 * Author Dmitry Kushneriov
 */

namespace App\Test;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use App\ApiPlatform\Test\ApiTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class CustomApiTestCase extends ApiTestCase
{
    protected function createUser(string $email, string $password, ?string $name = null): User
    {
        if ($name === null) {
            $name = substr($email, 0, strpos($email, '@'));
        }

        $encoder = self::getContainer()->get(PasswordHasherFactoryInterface::class);

        $user = new User();
        $user
            ->setEmail($email)
            ->setUsername($name);
        $user->setPassword($encoder->getPasswordHasher($user)->hash($password));

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function login(Client $client, string $email, string $password): void
    {
        $client->jsonRequest('POST', '/login', [
            'email' => $email,
            'password' => $password
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    protected function createUserAdnLogin(Client $client, string $email, string $password, ?string $name = null): User
    {
        $user = $this->createUser($email, $password, $name);
        $this->login($client, $email, $password);

        return $user;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
