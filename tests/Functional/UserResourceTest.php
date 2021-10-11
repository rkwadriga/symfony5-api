<?php declare(strict_types=1);
/**
 * Created 2021-10-10
 * Author Dmitry Kushneriov
 */

namespace Functional;

use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class UserResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateUser()
    {
        // 1. Create a new user adn check that response code is equals to 201
        $this->post('/api/users', [
            'email' => 'cheeseplease@example.com',
            'username' => 'cheeseplease',
            'password' => 'qwerty',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // 2. Test users login
        $this->login('cheeseplease@example.com', 'qwerty');
    }
}