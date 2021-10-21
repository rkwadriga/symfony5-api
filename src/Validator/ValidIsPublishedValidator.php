<?php

namespace App\Validator;

use App\Entity\CheeseListing;
use App\Security\SecurityHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidIsPublishedValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {}

    public function validate($value, Constraint $constraint)
    {
        /* @var ValidIsPublished $constraint */

        if (!$value instanceof CheeseListing) {
            throw new \LogicException(sprintf('Only %s is supported', CheeseListing::class));
        }

        if ($constraint->descriptionMinLength === 0) {
            return;
        }

        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($value);
        $previousIsPublished = $originalData['isPublished'] ?? false;

        if ($value->getIsPublished() === $previousIsPublished) {
            // "IsPublished" didn't change
            return;
        }

        // Admin can do anything!
        if ($this->security->isGranted(SecurityHelper::ROLE_ADMIN)) {
            return;
        }

        if ($value->getIsPublished() && strlen($value->getDescription()) < $constraint->descriptionMinLength) {
            $this->context->buildViolation("Can not publish: description is too short")
                ->atPath('description')
                ->addViolation();
        }

        if (!$value->getIsPublished()) {
            // Response code 403
            //throw new AccessDeniedException('Only admin user can unpublish cheese listing');
            // Response code 422
            $this->context->buildViolation('Only admin user can unpublish cheese listing')
                ->addViolation();
        }
    }
}
