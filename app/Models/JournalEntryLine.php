<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JournalEntryLine $line): void {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;

            if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
                throw new \InvalidArgumentException('Each journal line must have either debit or credit amount.');
            }
        });
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
