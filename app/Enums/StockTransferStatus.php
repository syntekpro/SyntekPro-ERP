<?php

namespace App\Enums;

enum StockTransferStatus: string
{
    case Pending = 'pending';
    case InTransit = 'in_transit';
    case Received = 'received';
}