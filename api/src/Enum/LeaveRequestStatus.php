<?php

namespace App\Enum;

use App\Entity\LeaveRequest;
use Random\Randomizer;

enum LeaveRequestStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    public static function randomLeaveRequest(): self
    {
        $leaveRequests = self::cases();
        return $leaveRequests[random_int(0, count($leaveRequests) - 1)];
    }
}
