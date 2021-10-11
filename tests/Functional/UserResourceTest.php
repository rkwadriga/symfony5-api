<?php declare(strict_types=1);
/**
 * Created 2021-10-10
 * Author Dmitry Kushneriov
 */

namespace Functional;

use App\Entity\User;
use App\Security\SecurityHelper;
use App\Test\Routes;
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

    public function testGetUser()
    {
        // 1. Create user and login it
        $user = $this->createUserAdnLogin('cheeseplease@example.com', 'qwerty');

        // 2. Set user's phone number
        $user->setPhoneNumber('(000) 111-222-333');
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        // 3. Test "GET /api/users/<id>" method - the response should has be successful, has a "username" param and doesn't have a "phoneNumber" param
        $this->request([Routes::URL_GET_USER, $user->getId()]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['username' => 'cheeseplease']);
        $this->assertArrayNotHasKey('phoneNumber', $this->getResponseParams());

        // 4. Create an admin user and chek is "GET /api/users/<id>" response has a "phoneNumber" param
        $this->createUserAdnLogin('admin@mail.com', '00000', null, SecurityHelper::ROLE_ADMIN);
        $this->request([Routes::URL_GET_USER, $user->getId()]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['phoneNumber' => '(000) 111-222-333']);
    }
}