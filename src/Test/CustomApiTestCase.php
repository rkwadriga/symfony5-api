<?php declare(strict_types=1);
/**
 * Created 2021-10-08
 * Author Dmitry Kushneriov
 */

namespace App\Test;

use App\Factory\UserFactory;
use App\Security\SecurityHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\User;
use Zenstruck\Foundry\Proxy;

abstract class CustomApiTestCase extends WebTestCase
{
    use ApiTestAssertionsTrait;
    use ApiRoutesTrait;

    protected ?KernelBrowser $client = null;

    protected function createUser(string $email, string $password, ?string $name = null, ?string $role = null): User
    {
        // Init client
        $this->getClient();

        if ($name === null) {
            $name = substr($email, 0, strpos($email, '@'));
        }
        $roles = $role !== null && in_array($role, SecurityHelper::ALLOWED_ROLES) ? [$role] : [];

        $encoder = self::getContainer()->get(PasswordHasherFactoryInterface::class);

        $user = new User();
        $user
            ->setEmail($email)
            ->setUsername($name)
            ->setRoles($roles)
        ;
        $user->setPassword($encoder->getPasswordHasher($user)->hash($password));

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function login($userOrEmail, string $password = UserFactory::DEFAULT_PASSWORD): void
    {
        if ($userOrEmail instanceof User || $userOrEmail instanceof Proxy) {
            $email = $userOrEmail->getEmail();
        } elseif (is_string($userOrEmail)) {
            $email = $userOrEmail;
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument 2 to "%s" should be a User, Foundry Proxy or string email, "%s" given',
                __METHOD__,
                is_object($userOrEmail) ? get_class($userOrEmail) : gettype($userOrEmail)
            ));
        }

        $this->request(Routes::URL_LOGIN, [
            'email' => $email,
            'password' => $password
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    protected function createUserAdnLogin(string $email, string $password, ?string $name = null, ?string $role = null): User
    {
        $user = $this->createUser($email, $password, $name, $role);
        $this->login($email, $password);

        return $user;
    }

    protected function get(mixed $route, array $params = [], array $headers = []): Crawler
    {
        return $this->request($route, $params, $headers, Request::METHOD_GET);
    }

    protected function post(mixed $route, array $params = [], array $headers = []): Crawler
    {
        return $this->request($route, $params, $headers, Request::METHOD_POST);
    }

    protected function put(mixed $route, array $params = [], array $headers = []): Crawler
    {
        return $this->request($route, $params, $headers, Request::METHOD_PUT);
    }

    protected function request(mixed $route, array $params = [], array $headers = [], ?string $method = null): Crawler
    {
        if ($method === null) {
            $method = $this->getRequestMethod($route);
        }
        $uri = $this->getRequestUri($route);

        $client = $this->getClient();
        $client->setServerParameter('CONTENT_TYPE', $this->requestContentType);
        $client->setServerParameter('HTTP_ACCEPT', $this->requestAssept);

        //return $this->getClient()->jsonRequest($method, $uri, $params, $headers);
        return $this->getClient()->request($method, $uri, [], [], $headers, json_encode($params));
    }

    protected function ldRequest(mixed $route, array $params = [], array $headers = [], ?string $method = null): Crawler
    {
        // 'application/ld+json'
        [$oldContentType, $oldAccept] = [$this->requestContentType, $this->requestAssept];
        [$this->requestContentType, $this->requestAssept] = ['application/ld+json', 'application/ld+json'];

        try {
            return $this->request($route, $params, $headers, $method);
        } finally {
            [$this->requestContentType, $this->requestAssept] = [$oldContentType, $oldAccept];
        }
    }

    protected function getClient(): KernelBrowser
    {
        if ($this->client === null) {
            $this->client = self::createClient();
        }

        return $this->client;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        // Init client
        $this->getClient();

        return self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function getRouter(): RouterInterface
    {
        // Init client
        $this->getClient();

        return self::getContainer()->get(RouterInterface::class);
    }
}
