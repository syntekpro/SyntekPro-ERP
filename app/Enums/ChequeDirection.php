<?php

namespace App\Enums;

enum ChequeDirection: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
}
