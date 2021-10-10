<?php

namespace App\Security\Voter;

use App\Security\SecurityHelper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\CheeseListing;

class CheeseListingVoter extends Voter
{
    public const ACTION_EDIT = 'EDIT';

    private const ALLOWED_ACTIONS = [
        self::ACTION_EDIT
    ];

    public function __construct(
        private Security $security
    ) {}

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, self::ALLOWED_ACTIONS) && $subject instanceof CheeseListing;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var CheeseListing $subject */

        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::ACTION_EDIT:
                return $subject->getOwner() === $user || $this->security->isGranted(SecurityHelper::ROLE_ADMIN);
        }

        throw new \Exception('Unhandled attribute %s', $attribute);
    }
}
