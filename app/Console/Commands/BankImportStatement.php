<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Services\Banking\BankStatementCsvImporter;
use Illuminate\Console\Command;

/**
 * Usage:
 *   php artisan bank:import-statement {bank_account_id} {csv_path} \
 *     --date-col=0 --desc-col=1 --amount-col=2 --ref-col=3 --date-format="d/m/Y"
 *
 * Or, for banks using separate debit/credit columns:
 *   php artisan bank:import-statement 1 statement.csv \
 *     --date-col=0 --desc-col=1 --debit-col=2 --credit-col=3
 */
class BankImportStatement extends Command
{
    protected $signature = 'bank:import-statement
        {bank_account_id}
        {csv_path}
        {--date-col= : 0-based column index for transaction date}
        {--desc-col= : 0-based column index for description}
        {--amount-col= : 0-based column index for signed amount (use instead of debit-col/credit-col)}
        {--debit-col= : 0-based column index for debit/outflow amount}
        {--credit-col= : 0-based column index for credit/inflow amount}
        {--ref-col= : 0-based column index for reference/memo}
        {--balance-col= : 0-based column index for running balance}
        {--date-format=Y-m-d : PHP date() format matching the CSV date column}
        {--no-header : Pass this if the CSV has no header row}';

    protected $description = 'Import a bank statement CSV into a bank account, using a manually specified column mapping';

    public function handle(BankStatementCsvImporter $importer): int
    {
        $bankAccount = BankAccount::find($this->argument('bank_account_id'));
        if ($bankAccount === null) {
            $this->error('Bank account not found.');

            return self::FAILURE;
        }

        $csvPath = $this->argument('csv_path');
        if (!is_file($csvPath)) {
            $this->error("File not found: {$csvPath}");

            return self::FAILURE;
        }

        $mapping = [];
        if ($this->option('date-col') !== null) {
            $mapping['transaction_date'] = (int) $this->option('date-col');
        }
        if ($this->option('desc-col') !== null) {
            $mapping['description'] = (int) $this->option('desc-col');
        }
        if ($this->option('amount-col') !== null) {
            $mapping['amount'] = (int) $this->option('amount-col');
        }
        if ($this->option('debit-col') !== null) {
            $mapping['debit'] = (int) $this->option('debit-col');
        }
        if ($this->option('credit-col') !== null) {
            $mapping['credit'] = (int) $this->option('credit-col');
        }
        if ($this->option('ref-col') !== null) {
            $mapping['reference'] = (int) $this->option('ref-col');
        }
        if ($this->option('balance-col') !== null) {
            $mapping['running_balance'] = (int) $this->option('balance-col');
        }

        try {
            $import = $importer->import(
                bankAccount: $bankAccount,
                csvPath: $csvPath,
                columnMapping: $mapping,
                originalFilename: basename($csvPath),
                importedByUserId: null,
                dateFormat: $this->option('date-format'),
                hasHeaderRow: !$this->option('no-header'),
            );
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Import #{$import->id} complete: {$import->imported_count} imported, {$import->skipped_count} skipped, {$import->row_count} total rows.");

        return self::SUCCESS;
    }
}
