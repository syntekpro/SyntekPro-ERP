<?php

namespace App\Services\Banking;

use App\Models\BankAccount;
use App\Models\BankStatementImport;
use App\Models\BankStatementLine;
use Illuminate\Support\Facades\DB;

/**
 * Imports a bank statement CSV using a configurable column map, since bank
 * export formats vary widely (date format, column order, single signed
 * "amount" column vs separate debit/credit columns).
 *
 * Expected $columnMapping keys (values are 0-based CSV column indices):
 *   - transaction_date (required)
 *   - description (required)
 *   - amount (required if not using debit/credit split)
 *   - debit / credit (required if not using a single amount column)
 *   - reference (optional)
 *   - running_balance (optional)
 *
 * Amount sign convention after import is always: positive = money in,
 * negative = money out. If the CSV uses separate debit/credit columns,
 * debit is treated as money out (negative) and credit as money in
 * (positive) - flip this in the mapping step if a given bank does the
 * opposite (some report debit as inflow from the bank's own perspective).
 */
class BankStatementCsvImporter
{
    public function import(
        BankAccount $bankAccount,
        string $csvPath,
        array $columnMapping,
        string $originalFilename,
        ?int $importedByUserId,
        string $dateFormat = 'Y-m-d',
        bool $hasHeaderRow = true,
    ): BankStatementImport {
        if (!isset($columnMapping['transaction_date'], $columnMapping['description'])) {
            throw new \InvalidArgumentException('Column mapping must include at least transaction_date and description.');
        }

        $usesSplitDebitCredit = isset($columnMapping['debit']) || isset($columnMapping['credit']);
        if (!$usesSplitDebitCredit && !isset($columnMapping['amount'])) {
            throw new \InvalidArgumentException('Column mapping must include either "amount", or "debit"/"credit".');
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open CSV file at {$csvPath}");
        }

        $rowCount = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $rowsToInsert = [];

        try {
            if ($hasHeaderRow) {
                fgetcsv($handle);
            }

            while (($row = fgetcsv($handle)) !== false) {
                $rowCount++;

                $parsed = $this->parseRow($row, $columnMapping, $dateFormat, $usesSplitDebitCredit);

                if ($parsed === null) {
                    $skippedCount++;

                    continue;
                }

                $rowsToInsert[] = $parsed;
                $importedCount++;
            }
        } finally {
            fclose($handle);
        }

        return DB::transaction(function () use (
            $bankAccount,
            $originalFilename,
            $columnMapping,
            $rowCount,
            $importedCount,
            $skippedCount,
            $importedByUserId,
            $rowsToInsert,
        ): BankStatementImport {
            $import = BankStatementImport::create([
                'bank_account_id' => $bankAccount->id,
                'original_filename' => $originalFilename,
                'column_mapping' => $columnMapping,
                'row_count' => $rowCount,
                'imported_count' => $importedCount,
                'skipped_count' => $skippedCount,
                'imported_by' => $importedByUserId,
            ]);

            foreach ($rowsToInsert as $row) {
                BankStatementLine::create([
                    'bank_statement_import_id' => $import->id,
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => $row['transaction_date'],
                    'description' => $row['description'],
                    'reference' => $row['reference'],
                    'amount' => $row['amount'],
                    'running_balance' => $row['running_balance'],
                    'match_status' => 'unmatched',
                ]);
            }

            return $import;
        });
    }

    /**
     * @return array{transaction_date: string, description: string, reference: ?string, amount: float, running_balance: ?float}|null
     */
    private function parseRow(array $row, array $columnMapping, string $dateFormat, bool $usesSplitDebitCredit): ?array
    {
        $dateRaw = $row[$columnMapping['transaction_date']] ?? null;
        $description = trim((string) ($row[$columnMapping['description']] ?? ''));

        if (empty($dateRaw) || $description === '') {
            return null;
        }

        $date = \DateTime::createFromFormat($dateFormat, trim($dateRaw));
        if ($date === false) {
            return null;
        }

        if ($usesSplitDebitCredit) {
            $debit = $this->parseAmount($row[$columnMapping['debit'] ?? null] ?? null);
            $credit = $this->parseAmount($row[$columnMapping['credit'] ?? null] ?? null);
            $amount = $credit - $debit;
        } else {
            $amount = $this->parseAmount($row[$columnMapping['amount']] ?? null);
        }

        $reference = isset($columnMapping['reference'])
            ? trim((string) ($row[$columnMapping['reference']] ?? '')) ?: null
            : null;

        $runningBalance = isset($columnMapping['running_balance'])
            ? $this->parseAmount($row[$columnMapping['running_balance']] ?? null)
            : null;

        return [
            'transaction_date' => $date->format('Y-m-d'),
            'description' => $description,
            'reference' => $reference,
            'amount' => $amount,
            'running_balance' => $runningBalance,
        ];
    }

    private function parseAmount(mixed $raw): float
    {
        if ($raw === null || $raw === '') {
            return 0.0;
        }

        // Strip thousands separators and currency symbols; keep digits, sign, decimal point.
        $cleaned = preg_replace('/[^0-9\-\.]/', '', (string) $raw);

        return (float) $cleaned;
    }
}
