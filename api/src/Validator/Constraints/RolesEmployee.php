<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class RolesEmployee extends Constraint
{
    public $message = 'You don\'t have permession to assign the role [{{ role }}].';
}
