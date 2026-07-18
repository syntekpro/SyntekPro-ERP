<?php

namespace App\Models;

use App\Enums\SalePaymentMethod;
use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'cashier_id',
        'idempotency_key',
        'invoice_number',
        'status',
        'sold_at',
        'subtotal',
        'vat_total',
        'total',
        'payment_method',
        'customer_id',
        'due_date',
        'outstanding_balance',
        'payload_hash',
        'zatca_qr_payload',
        'invoice_uuid',
        'invoice_hash',
        'sync_error',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SaleStatus::class,
            'payment_method' => SalePaymentMethod::class,
            'sold_at' => 'datetime',
            'due_date' => 'date',
            'synced_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'vat_total' => 'decimal:2',
            'total' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customerPayments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }
}
