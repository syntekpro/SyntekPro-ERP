<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_statement_import_id',
        'bank_account_id',
        'transaction_date',
        'description',
        'reference',
        'amount',
        'running_balance',
        'match_status',
        'matched_journal_entry_line_id',
        'matched_by',
        'matched_at',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'matched_at' => 'datetime',
        ];
    }

    public function statementImport(): BelongsTo
    {
        return $this->belongsTo(BankStatementImport::class, 'bank_statement_import_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function matchedJournalEntryLine(): BelongsTo
    {
        return $this->belongsTo(JournalEntryLine::class, 'matched_journal_entry_line_id');
    }

    public function matchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by');
    }

    public function isMatched(): bool
    {
        return $this->match_status === 'matched';
    }
}
