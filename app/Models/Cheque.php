<?php

namespace App\Models;

use App\Enums\ChequeDirection;
use App\Enums\ChequeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'direction',
        'cheque_number',
        'bank_name',
        'cheque_date',
        'amount',
        'status',
        'sale_id',
        'supplier_bill_id',
        'recorded_journal_entry_id',
        'cleared_journal_entry_id',
        'bounced_journal_entry_id',
        'cleared_at',
        'bounced_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'direction' => ChequeDirection::class,
            'status' => ChequeStatus::class,
            'cheque_date' => 'date',
            'amount' => 'decimal:2',
            'cleared_at' => 'date',
            'bounced_at' => 'date',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function supplierBill(): BelongsTo
    {
        return $this->belongsTo(SupplierBill::class);
    }

    public function recordedJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'recorded_journal_entry_id');
    }

    public function clearedJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'cleared_journal_entry_id');
    }

    public function bouncedJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'bounced_journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
