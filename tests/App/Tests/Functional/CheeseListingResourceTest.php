<?php declare(strict_types=1);
/**
 * Created 2021-10-07
 * Author Dmitry Kushneriov
 */

namespace App\Tests\Functional;

use App\ApiPlatform\Test\ApiTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CheeseListingResourceTest extends ApiTestCase
{
    public function testCreateCheeseListing(): void
    {
        $client = self::createClient();

        // 1. Check the "Unautorized" error
        $client->jsonRequest('POST', '/api/cheeses');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // 2. Create user
        $user = new User();
        $user
            ->setEmail('user1@mail.com')
            ->setUsername('user1')
            ->setPassword('$2y$13$tL.g9SXfy2LlgnvGAKIz8ufJdng.0x6BT.Nhwius77aL2LpJNwLwK')
        ;

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($user);
        $em->flush();

        // 3. Login user
        $client->jsonRequest('POST', '/login', [
            'email' => 'user1@mail.com',
            'password' => 'xxxxxx'
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
