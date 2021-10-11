<?php declare(strict_types=1);
/**
 * Created 2021-10-10
 * Author Dmitry Kushneriov
 */

namespace Functional;

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
            'email' => 'updated@example.com'
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['email' => 'updated@example.com']);
    }
}