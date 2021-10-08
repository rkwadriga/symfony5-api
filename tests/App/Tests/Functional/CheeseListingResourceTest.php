<?php declare(strict_types=1);
/**
 * Created 2021-10-07
 * Author Dmitry Kushneriov
 */

namespace App\Tests\Functional;

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
}
