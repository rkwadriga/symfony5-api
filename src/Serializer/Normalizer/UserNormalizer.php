<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;

class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(
        private Security $security,
        private ObjectNormalizer $normalizer
    ) {}

    public function normalize($object, $format = null, array $context = []): array
    {
        if ($this->userIsOwner($object)) {
            $context['groups'][] = 'owner:read';
        }

        $data = $this->normalizer->normalize($object, $format, $context);

        // Here: add, edit, or delete some data

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function userIsOwner(User $user): bool
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = $this->security->getUser();

        if (!$authenticatedUser) {
            return false;
        }

        return $authenticatedUser->getEmail() === $user->getEmail();
    }
}
