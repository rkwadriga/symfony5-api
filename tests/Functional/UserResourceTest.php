<?php declare(strict_types=1);
/**
 * Created 2021-10-10
 * Author Dmitry Kushneriov
 */

namespace Functional;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Security\SecurityHelper;
use App\ApiPlatform\Routes;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class UserResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateUser()
    {
        // 1. Create a new user adn check that response code is equals to 201
        $this->request(Routes::URL_CREATE_USER, [
            'email' => 'cheeseplease@example.com',
            'username' => 'cheeseplease',
            'password' => 'qwerty',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // 2. Test creation user with the same email
        $this->request(Routes::URL_CREATE_USER, [
            'email' => 'cheeseplease@example.com',
            'username' => 'cheeseplease1',
            'password' => 'qwerty',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        // 3. Test creation user with the same username
        $this->request(Routes::URL_CREATE_USER, [
            'email' => 'cheeseplease1@example.com',
            'username' => 'cheeseplease',
            'password' => 'qwerty',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        // 4. Test creation user without password
        $this->request(Routes::URL_CREATE_USER, [
            'email' => 'cheesepleas1e@example.com',
            'username' => 'cheeseplease1',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        // 5. Test users login
        $this->login('cheeseplease@example.com', 'qwerty');
    }

    public function testUpdateUser()
    {
        // 1. Create user and login it
        $user = $this->createUserAdnLogin('cheeseplease@example.com', 'qwerty');

        // 2. Update user's "username" field adn check that response status is 200
        $this->request([Routes::URL_UPDATE_USER, $user->getId()], [
            'email' => 'updated-first-time@example.com'
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['email' => 'updated-first-time@example.com']);

        // 3. Try to make itself admin - the "roles" param should be ignored
        $this->request([Routes::URL_UPDATE_USER, $user->getId()], [
            'email' => 'updated-second-time@example.com',
            'roles' => [SecurityHelper::ROLE_ADMIN]
        ]);
        $dbUser = $this->getEntityManager()->getRepository(User::class)->find($user->getId());
        $this->assertEquals('updated-second-time@example.com', $dbUser->getEmail());
        $this->assertEquals([SecurityHelper::ROLE_USER], $dbUser->getRoles());

        // 4. Create and login the admin user and test user roles updating
        /*$this->createUserAdnLogin('admin@example.com', 'admin', null, SecurityHelper::ROLE_ADMIN);
        $this->request([Routes::URL_UPDATE_USER, $user->getId()], [
            'roles' => [SecurityHelper::ROLE_ADMIN]
        ]);
        $dbUser = $this->getEntityManager()->getRepository(User::class)->find($user->getId());
        $this->assertEquals([SecurityHelper::ROLE_USER], $dbUser->getRoles());*/
    }

    public function testGetUserItem()
    {
        // Init the client
        $this->getClient();

        // 1. Create 2 users and admin
        $user1 = UserFactory::new()->withPhoneNumber()->create(['username' => 'cheesehead'])->disableAutoRefresh();
        $user2 = UserFactory::new()->create()->disableAutoRefresh();
        $admin = UserFactory::new()->admin()->create()->disableAutoRefresh();

        // 2. Login the second user and check "GET /api/users/<id>" method
        // ... the response should has be successful, has a "username" param and doesn't have a "phoneNumber" param
        // ... and have param "isMe" equals to false
        $this->login($user2);
        $this->request([Routes::URL_GET_USER, $user1->getId()]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => $user1->getUsername(),
            'isMe' => false,
            'isMvp' => true, // Username contains "cheese" word, so it's and MVP!
        ]);
        $this->assertArrayNotHasKey('phoneNumber', $this->getResponseParams());

        // 3. Login admin and chek is "GET /api/users/<id>" response has a "phoneNumber" param and param "isMe" equals to false
        $this->login($admin);
        $this->request([Routes::URL_GET_USER, $user1->getId()]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['phoneNumber' => $user1->getPhoneNumber(), 'isMe' => false]);

        // 4. Login the first user user and chek is "GET /api/users/<id>" response has a "phoneNumber" param, adn "isMe" param equals to true
        $this->login($user1);
        $this->request([Routes::URL_GET_USER, $user1->getId()]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['phoneNumber' => $user1->getPhoneNumber(), 'isMe' => true]);
    }

    public function testGetUsersCollection()
    {
        static $USERS_LIST_SIZE = 5;

        // 3. Create a few users
        for ($i = 1; $i <= $USERS_LIST_SIZE; $i++) {
            $user = $this->createUser("cheeseplease_{$i}@example.com", 'qwerty');
            $user->setPhoneNumber("(000) {$i}{$i}{$i}-222-333");
            $em = $this->getEntityManager();
            $em->persist($user);
            $em->flush();
        }

        // 2. Login the last of created users
        /** @var User $user */
        $this->login($user, 'qwerty');

        // 3. Get users list anc chek that there are 5 users returned,
        // ... each of them has "isMe" and "email" params
        // ... and only the last user has "isMe" field equals to true
        $this->request(Routes::URL_GET_USERS);
        $this->assertResponseIsSuccessful();
        $users = $this->getResponseParams();
        $this->assertCount($USERS_LIST_SIZE, $users);
        foreach ($users as $userParams) {
            $this->assertArrayHasKey('isMe', $userParams);
            $this->assertArrayHasKey('email', $userParams);
            $this->assertEquals($userParams['email'] === $user->getEmail(), $userParams['isMe']);
        }
    }
}