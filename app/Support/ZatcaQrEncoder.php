<?php

namespace App\Support;

class ZatcaQrEncoder
{
    public static function encode(array $fields): string
    {
        $binary = '';

        foreach ($fields as $tag => $value) {
            $stringValue = (string) $value;
            $binary .= chr((int) $tag);
            $binary .= chr(strlen($stringValue));
            $binary .= $stringValue;
        }

        return base64_encode($binary);
    }
}
