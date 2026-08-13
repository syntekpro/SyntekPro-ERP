<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZatcaCertificate extends Model
{
    protected $fillable = [
        'environment',
        'csid_type',
        'binary_security_token',
        'secret',
        'request_id',
        'private_key_encrypted',
        'csr',
        'issued_at',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public static function activeProduction(): ?self
    {
        return static::query()
            ->where('csid_type', 'production')
            ->whereNull('revoked_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('issued_at')
            ->first();
    }

    public static function activeCompliance(): ?self
    {
        return static::query()
            ->where('csid_type', 'compliance')
            ->whereNull('revoked_at')
            ->latest('issued_at')
            ->first();
    }
}
