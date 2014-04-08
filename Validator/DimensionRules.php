<?php

// src/CKM/AppBundle/Validator/DimensionRules.php
namespace CKM\AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/** @Annotation */
class DimensionRules extends Constraint
{
    public function validatedBy()
    {
        return 'dimension_rules';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}