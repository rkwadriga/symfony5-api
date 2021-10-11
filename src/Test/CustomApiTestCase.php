<?php declare(strict_types=1);
/**
 * Created 2021-10-08
 * Author Dmitry Kushneriov
 */

namespace App\Test;

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

abstract class CustomApiTestCase extends WebTestCase
{
    use ApiTestAssertionsTrait;

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

    protected function login(string $email, string $password): void
    {
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
        [$roteName, $parameters] = is_array($route) ? $route : [$route, []];
        if (!is_array($parameters)) {
            $parameters = ['id' => $parameters];
        }

        if ($method === null) {
            $methods = $this->getRouter()->getRouteCollection()->get($roteName)->getMethods();
            $method = array_shift($methods);
        }

        $uri = $this->getRouter()->generate($roteName, $parameters);

        return $this->getClient()->jsonRequest($method, $uri, $params, $headers);
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

    public function getRouter(): RouterInterface
    {
        // Init client
        $this->getClient();

        return self::getContainer()->get(RouterInterface::class);
    }
}
