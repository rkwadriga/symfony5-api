<?php declare(strict_types=1);
/**
 * Created 2021-10-07
 * Author Dmitry Kushneriov
 */

namespace Functional;

use App\Factory\CheeseListingFactory;
use App\Factory\CheeseNotificationFactory;
use App\Factory\UserFactory;
use App\ApiPlatform\Routes;
use App\Entity\CheeseListing;
use App\Security\SecurityHelper;
use App\Test\CustomApiTestCase;
use Symfony\Component\HttpFoundation\Response;
//use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CheeseListingResourceTest extends CustomApiTestCase
{
    //use ReloadDatabaseTrait;
    use Factories;
    use ResetDatabase;

    public function testCreateCheeseListing(): void
    {
        // 1. Check the "Unautorized" error
        $this->request(Routes::URL_CREATE_CHEESE_LISTING);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // 2. Create user and login
        $authenticatedUser = $this->createUserAdnLogin('authenticated@mail.com', 'authenticated');

        // 3. Create the other user
        $otherUser = $this->createUser('other@mail.com', 'other');

        // 4. Check the "Unprocessable entity" error (when the "not blank" validation for some fields is failed)
        $this->request(Routes::URL_CREATE_CHEESE_LISTING);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        // 5. Create a cheese listing without owner and check s it's owner was automatically set to logged-in user
        $createCheeseRequestString = $this->getRequestAsString(Routes::URL_CREATE_CHEESE_LISTING);
        $cheeseData = [
            'title' => 'Mystery cheese... kinda green',
            'description' => 'What mysteries does it hold?',
            'price' => 5000,
        ];
        $this->request(Routes::URL_CREATE_CHEESE_LISTING, $cheeseData);
        $this->assertResponseIsSuccessful();
        $newCheeseID = $this->getResponseParams('id');
        $this->assertNotNull($newCheeseID, sprintf('The response of "%s" request does not contain the "id" param: %s',
            $createCheeseRequestString,
            $this->getClient()->getResponse()->getContent()
        ));
        /** @var CheeseListing $newCheese */
        $newCheese = $this->getEntityManager()->getRepository(CheeseListing::class)->find($newCheeseID);
        $this->assertNotNull($newCheese);
        $this->assertEquals($authenticatedUser->getId(), $newCheese->getOwner()->getId());

        // 6. Check if user can not create a cheese with the owner not himself
        $cheeseData['title'] = 'New mystery cheese';
        $ownerUri = $this->getRouter()->generate(Routes::URL_GET_USER, ['uuid' => $otherUser->getUuid()]);
        $this->request(Routes::URL_CREATE_CHEESE_LISTING, $cheeseData + ['owner' => $ownerUri]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, 'not passing the correct owner');

        // 7. Check if user can create a cheese with the owner himself
        $ownerUri = $this->getRouter()->generate(Routes::URL_GET_USER, ['uuid' => $authenticatedUser->getUuid()]);
        $this->request(Routes::URL_CREATE_CHEESE_LISTING, $cheeseData + ['owner' => $ownerUri]);
        $this->assertResponseIsSuccessful();
    }

    public function testUpdateCheeseListing(): void
    {
        // 1. Create users
        $user1 = $this->createUser('user1@mail.com', '11111');
        $user2 = $this->createUser('user2@mail.com', '22222');

        // 2. Create a new cheese listing and set just created use as it's owner
        $cheeseListing = new CheeseListing('Block of chedder');
        $cheeseListing
            ->setPrice(1000)
            ->setDescription('mmmm')
            ->setOwner($user1)
            ->setIsPublished(true)
        ;

        $em = $this->getEntityManager();
        $em->persist($cheeseListing);
        $em->flush();

        // 3. Login the first user
        $this->login('user1@mail.com', '11111');
        // ... and update his cheese listing
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $cheeseListing->getId()], [
            'title' => 'Updated',
        ]);
        $this->assertResponseIsSuccessful();

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
        $this->assertResponseIsSuccessful();
    }

    public function testGetCheeseListingCollection(): void
    {
        // 1. Create user
        $user = $this->createUser('needcheese@test.com', 'qwerty');

        // 2. Create a few cheeses
        $cheeseListing1 = new CheeseListing('cheese1');
        $cheeseListing1
            ->setOwner($user)
            ->setPrice(1000)
            ->setDescription('Cheese 1')
        ;
        $cheeseListing2 = new CheeseListing('cheese2');
        $cheeseListing2
            ->setOwner($user)
            ->setPrice(2000)
            ->setDescription('Cheese 2')
            ->setIsPublished(true)
        ;
        $cheeseListing3 = new CheeseListing('cheese3');
        $cheeseListing3
            ->setOwner($user)
            ->setPrice(3000)
            ->setDescription('Cheese 3')
            ->setIsPublished(true)
        ;
        $em = $this->getEntityManager();
        $em->persist($cheeseListing1);
        $em->persist($cheeseListing2);
        $em->persist($cheeseListing3);
        $em->flush();

        // 3. Get cheese collection and check if response contains only "published" cheeses
        $this->ldRequest(Routes::URL_GET_CHEESE_LISTINGS);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['hydra:totalItems' => 2]);
    }

    public function testGetCheeseListingItem(): void
    {
        // 1. Create user
        $user = $this->createUser('needcheese@test.com', 'qwerty');

        // 2. Create 2 cheeses - published and unpublished
        $unpublishedCheeseListing = new CheeseListing('cheese1');
        $unpublishedCheeseListing
            ->setOwner($user)
            ->setPrice(1000)
            ->setDescription('Cheese 1')
            ->setIsPublished(false)
        ;
        $publishedCheeseListing = new CheeseListing('cheese1');
        $publishedCheeseListing
            ->setOwner($user)
            ->setPrice(1000)
            ->setDescription('Cheese 1')
            ->setIsPublished(true)
        ;
        $em = $this->getEntityManager();
        $em->persist($unpublishedCheeseListing);
        $em->persist($publishedCheeseListing);
        $em->flush();

        // 3. Unpublished cheese should not be visible
        $this->ldRequest([Routes::URL_GET_CHEESE_LISTING, $unpublishedCheeseListing->getId()]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        // 4. login user and check is published cheese should be visible
        $this->login($user->getEmail(), 'qwerty');
        $this->ldRequest([Routes::URL_GET_CHEESE_LISTING, $publishedCheeseListing->getId()]);
        $this->assertResponseIsSuccessful();

        // 5. Get user's info and check that user has only one cheese in it's "cheeseListings" param
        $getUserRequestString = $this->getRequestAsString([Routes::URL_GET_USER, ['uuid' => $user->getUuid()]]);
        $this->request([Routes::URL_GET_USER, ['uuid' => $user->getUuid()]]);
        $cheeseListings = $this->getResponseParams('cheeseListings');
        $this->assertIsArray($cheeseListings, sprintf('The response of "%s" request does not contain the "cheeseListings" param (array): %s',
            $getUserRequestString,
            $this->getClient()->getResponse()->getContent()
        ));
        $this->assertCount(1, $cheeseListings);
    }

    public function testPublishCheeseListing(): void
    {
        // Init client
        $this->getClient();

        // 1. Create a user
        $user = UserFactory::new()->create();

        // 2. Create a new cheese listing and set just created user as it's owner
        $cheeseListing = CheeseListingFactory::new()
            ->withLongDescription()
            ->create(['owner' => $user])
        ;

        // 3. Login user and check is cheese publication request successful
        $this->login($user);
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $cheeseListing->getId()], [
            'isPublished' => true,
        ]);
        $this->assertResponseIsSuccessful();

        // 4. Check is cheese published
        $cheeseListing->refresh();
        $this->assertTrue($cheeseListing->getIsPublished());

        // 5. Test cheese publishing notification
        CheeseNotificationFactory::repository()->assert()->count(1, 'There should be one notification about being published.');

        // 6. Update cheese listing again and check is there is steel one notification
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $cheeseListing->getId()], [
            'isPublished' => true,
        ]);
        CheeseNotificationFactory::repository()->assert()->count(1);
    }

    public function testPublishCheeseListingValidation(): void
    {
        // Load the client
        $this->getClient();

        // 1. Create user and admin
        $user = UserFactory::new()->create();
        $admin = UserFactory::new()->create(['roles' => [SecurityHelper::ROLE_ADMIN]]);

        // 2. Create a cheese with a short description
        // ... acn check is user and admin can publish this cheese
        $shortCheeseListing = CheeseListingFactory::new()->create(['owner' => $user, 'description' => 'Short']);
        // 2.1. Check for user
        $this->login($user);
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $shortCheeseListing->getId()], [
            'isPublished' => true,
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, 'Description is too short');
        // 2.1. Check for admin
        $this->login($admin);
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $shortCheeseListing->getId()], [
            'isPublished' => true,
        ]);
        $this->assertResponseIsSuccessful();
        $shortCheeseListing->refresh();
        $this->assertTrue($shortCheeseListing->getIsPublished());

        // 3. The same like for 2 but with long description
        $longCheeseListing = CheeseListingFactory::new()
            ->withLongDescription()
            ->create(['owner' => $user])
        ;
        // 3.1. Check for user
        $this->login($user);
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $longCheeseListing->getId()], [
            'isPublished' => true,
        ]);
        $this->assertResponseIsSuccessful();
        $longCheeseListing->refresh();
        $this->assertTrue($longCheeseListing->getIsPublished());
        // 3.1. Check for admin
        $this->login($admin);
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $longCheeseListing->getId()], [
            'isPublished' => true,
        ]);
        $this->assertResponseIsSuccessful();

        // 4. Only admin cah unpublish cheese
        // 4.1. Check for user
        $this->login($user);
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $longCheeseListing->getId()], [
            'isPublished' => false,
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, 'Only admin cah unpublish the cheese listing');
        // 4.2. Check for admin
        $this->login($admin);
        $this->request([Routes::URL_UPDATE_CHEESE_LISTING, $longCheeseListing->getId()], [
            'isPublished' => false,
        ]);
        $this->assertResponseIsSuccessful();
        $longCheeseListing->refresh();
        $this->assertFalse($longCheeseListing->getIsPublished());
    }
}
