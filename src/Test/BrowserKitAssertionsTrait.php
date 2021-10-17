<?php
/**
 * Created 2021-10-11
 * Author Dmitry Kushneriov
 */

namespace App\Test;

use Symfony\Component\HttpClient\Exception\JsonException;

trait BrowserKitAssertionsTrait
{
    protected string $requestContentType = 'application/json';
    protected string $requestAssept = 'application/json';

    public function getResponseParams(mixed $params = null, mixed $defaultValue = null): mixed
    {
        // Decode content
        try {
            $content = json_decode($this->getClient()->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new JsonException($e->getMessage(), $e->getCode());
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException(json_last_error_msg(), json_last_error());
        }

        if (!is_array($content)) {
            throw new JsonException(sprintf('JSON content was expected to decode to an array, %s returned.', gettype($content)));
        }

        if (empty($params)) {
            return $content;
        }

        if (!is_array($params)) {
            return array_key_exists($params, $content) ? $content[$params] : $defaultValue;
        }

        return array_filter($content, function ($key) use ($params) {
            return in_array($key, $params);
        }, ARRAY_FILTER_USE_KEY);
    }
}