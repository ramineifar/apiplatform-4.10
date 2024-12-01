<?php

namespace App\Enum;

enum EmployeeRoles: string
{
    case ROLE_EMPLOYEE = 'ROLE_EMPLOYEE';
    case ROLE_MANAGER = 'ROLE_MANAGER';
    case ROLE_ADMIN = 'ROLE_ADMIN';
}
