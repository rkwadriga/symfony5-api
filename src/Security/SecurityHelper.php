<?php declare(strict_types=1);
/**
 * Created 2021-10-10
 * Author Dmitry Kushneriov
 */

namespace App\Security;

class SecurityHelper
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ALLOWED_ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
    ];
}