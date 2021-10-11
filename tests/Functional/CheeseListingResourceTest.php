<?php declare(strict_types=1);
/**
 * Created 2021-10-07
 * Author Dmitry Kushneriov
 */

namespace Functional;

use App\Test\Routes;
use App\Entity\CheeseListing;
use App\Security\SecurityHelper;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class CheeseListingResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateCheeseListing(): void
    {
        // 1. Check the "Unautorized" error
        $this->request(Routes::URL_CREATE_CHEESE_LISTING);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // 2. Create user and login
        $this->createUserAdnLogin('user1@mail.com', 'xxxxxx');

        // 3. Check the "Unprocessable entity" error (when the "not blank" validation for some fields is failed)
        $this->request(Routes::URL_CREATE_CHEESE_LISTING);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateCheeseListing()
    {
        // 1. Create users
        $user1 = $this->createUser('user1@mail.com', '11111');
        $user2 = $this->createUser('user2@mail.com', '22222');

        // 2. Create a new cheese listing and set just created use as it's owner
        $cheeseListing = new CheeseListing('Block of chedder');
        $cheeseListing
            ->setPrice(1000)
            ->setDescription('mmmm')
            ->setOwner($user1);

        $em = $this->getEntityManager();
        $em->persist($cheeseListing);
        $em->flush();

        // 3. Login the first user
        $this->login('user1@mail.com', '11111');
        // ... and update his cheese listing
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $cheeseListing->getId()], [
            'title' => 'Updated',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // 4. Login the second user
        $this->login('user2@mail.com', '22222');
        // ... and try to update cheese listing from his account
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $cheeseListing->getId()], [
            'title' => 'Updated',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // 5. The second user tries to make himself an owner of else's cheese listing
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $cheeseListing->getId()], [
            'owner' => '/api/users/' . $user2->getId(),
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // 6. Create and login the admin user and chek is he can edit else's cheese listing
        $this->createUserAdnLogin('admin@mail.com', '00000', null, SecurityHelper::ROLE_ADMIN);
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $cheeseListing->getId()], [
            'title' => 'Updated by admin',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
