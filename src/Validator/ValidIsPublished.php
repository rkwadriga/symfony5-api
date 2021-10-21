<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Doctrine\Common\Annotations\Annotation\Target;

#[
    \Attribute,
    Target(["CLASS"])
]
class ValidIsPublished extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $message = 'The value "{{ value }}" is not valid.';

    public int $descriptionMinLength = 0;

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
