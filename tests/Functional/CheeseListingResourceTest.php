<?php declare(strict_types=1);
/**
 * Created 2021-10-07
 * Author Dmitry Kushneriov
 */

namespace Functional;

use App\Entity\CheeseListing;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class CheeseListingResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateCheeseListing(): void
    {
        $client = self::createClient();

        // 1. Check the "Unautorized" error
        $client->jsonRequest('POST', '/api/cheeses');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // 2. Create user and login
        $this->createUserAdnLogin($client, 'user1@mail.com', 'xxxxxx');

        // 3. Check the "Unprocessable entity" error (when the "not blank" validation for some fields is failed)
        $client->jsonRequest('POST', '/api/cheeses');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateCheeseListing()
    {
        $client = self::createClient();

        // 1. Create user and login
        $user1 = $this->createUser('user1@mail.com', '11111');

        // 2. Create a new cheese listing and set just created use as it's owner
        $cheeseListing = new CheeseListing('Block of chedder');
        $cheeseListing
            ->setPrice(1000)
            ->setDescription('mmmm')
            ->setOwner($user1);

        $em = $this->getEntityManager();
        $em->persist($cheeseListing);
        $em->flush();

        // 3. Update cheese listing with correct user
        $this->login($client, 'user1@mail.com', '11111');
        $client->jsonRequest('PUT', '/api/cheeses/' . $cheeseListing->getId(), [
            'title' => 'Updated',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // 4. Create a second user, login it and try to update cheese listing from his account
        $user2 = $this->createUser('user2@mail.com', '22222');
        $this->login($client, 'user2@mail.com', '22222');
        $client->jsonRequest('PUT', '/api/cheeses/' . $cheeseListing->getId(), [
            'title' => 'Updated',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
