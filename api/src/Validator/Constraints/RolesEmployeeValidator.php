<?php

namespace App\Validator\Constraints;

use App\Enum\EmployeeRoles;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class RolesEmployeeValidator extends ConstraintValidator
{
    private UserInterface $user;
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        $this->user = $this->tokenStorage->getToken()->getUser();
        if ($this->isEmployeeUser() || $this->isManagerUser()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ role }}', implode(',', array_filter($value, fn ($item) => $item !== EmployeeRoles::ROLE_EMPLOYEE->value )))
                ->addViolation();
        }
    }

    private function isEmployeeUser(): bool
    {
        return $this->user->getRoles() === [EmployeeRoles::ROLE_EMPLOYEE->value];
    }

    private function isManagerUser(): bool
    {
        return !in_array(EmployeeRoles::ROLE_ADMIN->value, $this->user->getRoles(), true)
            && in_array(EmployeeRoles::ROLE_MANAGER->value, $this->user->getRoles(), true);
    }
}
