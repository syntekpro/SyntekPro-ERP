<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Queued = 'queued';
    case Synced = 'synced';
    case Duplicate = 'duplicate';
    case Rejected = 'rejected';
}
