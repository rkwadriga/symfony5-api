<?php
/**
 * Created 2021-10-11
 * Author Dmitry Kushneriov
 */

namespace App\Test;

use App\Test\Constraint\ArraySubset;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception as HttpException;
use Symfony\Component\HttpClient\Exception\JsonException;

trait ApiTestAssertionsTrait
{
    use BrowserKitAssertionsTrait;

    /**
     * Asserts that the retrieved JSON contains the specified subset.
     *
     * This method delegates to static::assertArraySubset().
     *
     * @param array|string $subset
     * @param bool $checkForObjectIdentity
     * @param string $message
     *
     * @throws HttpException\ClientExceptionInterface
     * @throws HttpException\DecodingExceptionInterface
     * @throws HttpException\RedirectionExceptionInterface
     * @throws HttpException\ServerExceptionInterface
     * @throws HttpException\TransportExceptionInterface
     * @throws JsonException
     * @throws \Exception
     */
    public function assertJsonContains($subset, bool $checkForObjectIdentity = true, string $message = ''): void
    {
        if (is_string($subset)) {
            $subset = json_decode($subset, true);
        }
        if (!is_array($subset)) {
            throw new \InvalidArgumentException('$subset must be array or string (JSON array or JSON object)');
        }

        $this->assertArraySubset($subset, $this->getResponseParams(), $checkForObjectIdentity, $message);
    }


    /**
     * Asserts that an array has a specified subset.
     *
     * Imported from dms/phpunit-arraysubset, because the original constraint has been deprecated.
     *
     * @copyright Sebastian Bergmann <sebastian@phpunit.de>
     * @copyright Rafael Dohms <rdohms@gmail.com>
     *
     * @param $subset
     * @param $array
     * @param bool $checkForObjectIdentity
     * @param string $message
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        $constraint = new ArraySubset($subset, $checkForObjectIdentity);

        $this->assertThat($array, $constraint, $message);
    }
}