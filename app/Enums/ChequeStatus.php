<?php

namespace App\Enums;

enum ChequeStatus: string
{
    case Pending = 'pending';
    case Cleared = 'cleared';
    case Bounced = 'bounced';
    case Cancelled = 'cancelled';
}
