<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1000', 'name' => 'Assets', 'account_type' => AccountType::Asset, 'parent_code' => null],
            ['code' => '1010', 'name' => 'Cash on Hand', 'account_type' => AccountType::Asset, 'parent_code' => '1000'],
            ['code' => '1020', 'name' => 'Bank Account', 'account_type' => AccountType::Asset, 'parent_code' => '1000'],
            ['code' => '1100', 'name' => 'Accounts Receivable Control', 'account_type' => AccountType::Asset, 'parent_code' => '1000'],
            ['code' => '1150', 'name' => 'Due from Supplier - Returns', 'account_type' => AccountType::Asset, 'parent_code' => '1000'],
            ['code' => '1200', 'name' => 'Inventory', 'account_type' => AccountType::Asset, 'parent_code' => '1000'],
            ['code' => '1300', 'name' => 'VAT Receivable', 'account_type' => AccountType::Asset, 'parent_code' => '1000'],

            ['code' => '2000', 'name' => 'Liabilities', 'account_type' => AccountType::Liability, 'parent_code' => null],
            ['code' => '2100', 'name' => 'Accounts Payable Control', 'account_type' => AccountType::Liability, 'parent_code' => '2000'],
            ['code' => '2200', 'name' => 'VAT Payable', 'account_type' => AccountType::Liability, 'parent_code' => '2000'],

            ['code' => '3000', 'name' => 'Equity', 'account_type' => AccountType::Equity, 'parent_code' => null],
            ['code' => '3100', 'name' => 'Owner Capital', 'account_type' => AccountType::Equity, 'parent_code' => '3000'],
            ['code' => '3200', 'name' => 'Retained Earnings', 'account_type' => AccountType::Equity, 'parent_code' => '3000'],

            ['code' => '4000', 'name' => 'Revenue', 'account_type' => AccountType::Revenue, 'parent_code' => null],
            ['code' => '4100', 'name' => 'Sales Revenue', 'account_type' => AccountType::Revenue, 'parent_code' => '4000'],

            ['code' => '5000', 'name' => 'Expenses', 'account_type' => AccountType::Expense, 'parent_code' => null],
            ['code' => '5100', 'name' => 'Cost of Goods Sold', 'account_type' => AccountType::Expense, 'parent_code' => '5000'],
            ['code' => '5200', 'name' => 'Rent Expense', 'account_type' => AccountType::Expense, 'parent_code' => '5000'],
            ['code' => '5300', 'name' => 'Utilities Expense', 'account_type' => AccountType::Expense, 'parent_code' => '5000'],
            ['code' => '5400', 'name' => 'Salaries Expense', 'account_type' => AccountType::Expense, 'parent_code' => '5000'],
            ['code' => '5500', 'name' => 'Inventory Write-off Expense', 'account_type' => AccountType::Expense, 'parent_code' => '5000'],
        ];

        foreach ($accounts as $seedAccount) {
            $parentId = null;

            if ($seedAccount['parent_code'] !== null) {
                $parentId = Account::query()->where('code', $seedAccount['parent_code'])->value('id');
            }

            Account::query()->updateOrCreate(
                ['code' => $seedAccount['code']],
                [
                    'name' => $seedAccount['name'],
                    'account_type' => $seedAccount['account_type']->value,
                    'parent_id' => $parentId,
                    'is_active' => true,
                ]
            );
        }
    }
}
