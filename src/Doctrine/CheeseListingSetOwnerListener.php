<?php declare(strict_types=1);
/**
 * Created 2021-10-17
 * Author Dmitry Kushneriov
 */

namespace App\Doctrine;

use App\Entity\CheeseListing;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class CheeseListingSetOwnerListener
{
    public function __construct(
        private Security $security
    ) {}

    public function prePersist(CheeseListing $cheeseListing): void
    {
        if ($cheeseListing->getOwner() !== null) {
            return;
        }

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $cheeseListing->setOwner($user);
        }
    }
}