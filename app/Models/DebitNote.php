<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DebitNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'debit_note_number',
        'supplier_bill_id',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'journal_entry_id',
        'note_date',
        'subtotal',
        'vat_total',
        'total',
        'applied_to_bill_balance',
        'excess_amount',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'note_date' => 'date',
            'subtotal' => 'decimal:2',
            'vat_total' => 'decimal:2',
            'total' => 'decimal:2',
            'applied_to_bill_balance' => 'decimal:2',
            'excess_amount' => 'decimal:2',
        ];
    }

    public function supplierBill(): BelongsTo
    {
        return $this->belongsTo(SupplierBill::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DebitNoteItem::class);
    }
}