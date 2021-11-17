<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;

class UserNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'USER_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private Security $security
    ) {}

    public function normalize($object, $format = null, array $context = [])
    {
        $isOwner = $this->userIsOwner($object);

        if ($isOwner) {
            $context['groups'][] = 'owner:read';
        }

        $context[self::ALREADY_CALLED] = true;

        // Bad decision: the API documentation know about this field nothing. So, look on the DataProvider
        //$data = $this->normalizer->normalize($object, $format, $context);
        //$data['isMe'] = $isOwner;
        //return $data;

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }


    private function userIsOwner(User $user): bool
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = $this->security->getUser();

        return $authenticatedUser !== null && $authenticatedUser->getEmail() === $user->getEmail();
    }
}
