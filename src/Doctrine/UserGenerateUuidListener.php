<?php declare(strict_types=1);
/**
 * Created 2021-11-20
 * Author Dmitry Kushneriov
 */

namespace App\Doctrine;

use App\Entity\User;
use Ramsey\Uuid\Uuid;

class UserGenerateUuidListener
{
    public function prePersist(User $user)
    {
        if ($user->getUuid() === null) {
            $user->setUuid(Uuid::uuid4());
        }
    }
}