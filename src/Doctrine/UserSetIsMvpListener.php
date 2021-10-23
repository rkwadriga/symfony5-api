<?php declare(strict_types=1);
/**
 * Created 2021-10-23
 * Author Dmitry Kushneriov
 */

namespace App\Doctrine;

use App\Entity\User;

class UserSetIsMvpListener
{
    public function postLoad(User $user): void
    {
        // Variant for fast calculations. For hard calculations look for something like Symfony\Component\String\LazyString
        $user->setIsMvp(str_contains($user->getUsername(), 'cheese'));
    }
}