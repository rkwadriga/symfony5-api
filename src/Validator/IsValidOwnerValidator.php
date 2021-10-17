<?php declare(strict_types=1);

namespace App\Validator;

use App\Entity\User;
use App\Security\SecurityHelper;
use \InvalidArgumentException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidOwnerValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security
    ) {}

    public function validate($value, Constraint $constraint): void
    {
        /* @var IsValidOwner $constraint */

        if ($value === null || $value === '') {
            return;
        }

        $user = $this->security->getUser();
        if ($user === null) {
            $this->context->buildViolation($constraint->anonymousMessage)->addViolation();
            return;
        }

        if ($this->security->isGranted(SecurityHelper::ROLE_ADMIN)) {
            return;
        }

        if (!$value instanceof User) {
            throw new InvalidArgumentException('@IsValidOwner constraint must be put on a property containing a User object');
        }

        if ($value !== $user) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
