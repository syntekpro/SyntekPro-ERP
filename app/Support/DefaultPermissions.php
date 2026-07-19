<?php

namespace App\Support;

use App\Enums\UserRole;

class DefaultPermissions
{
    public static function labels(): array
    {
        return [
            'accounts.view' => 'View accounts',
            'accounts.create' => 'Create accounts',
            'accounts.update' => 'Update accounts',
            'accounts.delete' => 'Delete accounts',
            'customers.view' => 'View customers',
            'customers.create' => 'Create customers',
            'customers.update' => 'Update customers',
            'credit_notes.view' => 'View credit notes',
            'credit_notes.create' => 'Create credit notes',
            'debit_notes.view' => 'View debit notes',
            'debit_notes.create' => 'Create debit notes',
            'journal_entries.view' => 'View journal entries',
            'journal_entries.create' => 'Create journal entries',
            'products.view' => 'View products',
            'products.create' => 'Create products',
            'products.update' => 'Update products',
            'products.delete' => 'Delete products',
            'purchase_orders.view' => 'View purchase orders',
            'purchase_orders.create' => 'Create purchase orders',
            'purchase_orders.update' => 'Update purchase orders',
            'purchase_orders.submit' => 'Submit purchase orders',
            'purchase_orders.receive' => 'Receive purchase orders',
            'purchase_orders.close' => 'Close purchase orders',
            'settings.manage' => 'Manage business settings',
            'shops.view_any' => 'View all shops',
            'shops.view' => 'View assigned shop',
            'shops.create' => 'Create shops',
            'shops.update' => 'Update shops',
            'shops.delete' => 'Delete shops',
            'shop_stock.view' => 'View shop stock',
            'shop_stock.update' => 'Update shop stock',
            'stock_transfers.view' => 'View stock transfers',
            'stock_transfers.create' => 'Create stock transfers',
            'stock_transfers.mark_in_transit' => 'Mark stock transfers in transit',
            'stock_transfers.receive' => 'Receive stock transfers',
            'supplier_bills.view' => 'View supplier bills',
            'supplier_bills.record_payment' => 'Record supplier bill payments',
            'suppliers.view' => 'View suppliers',
            'suppliers.create' => 'Create suppliers',
            'suppliers.update' => 'Update suppliers',
            'users.view' => 'View users',
            'users.create' => 'Create users',
            'users.update' => 'Update users',
            'users.delete' => 'Delete users',
            'warehouses.view' => 'View warehouses',
            'warehouses.create' => 'Create warehouses',
            'warehouses.update' => 'Update warehouses',
            'warehouses.delete' => 'Delete warehouses',
        ];
    }

    public static function roleMap(): array
    {
        return [
            UserRole::SuperAdmin->value => array_keys(self::labels()),
            UserRole::Accountant->value => [
                'accounts.view', 'customers.view', 'customers.create', 'customers.update',
                'credit_notes.view', 'credit_notes.create', 'debit_notes.view', 'debit_notes.create',
                'journal_entries.view', 'journal_entries.create', 'products.view', 'purchase_orders.view',
                'purchase_orders.create', 'purchase_orders.update', 'purchase_orders.submit',
                'purchase_orders.receive', 'purchase_orders.close', 'settings.manage',
                'supplier_bills.view', 'supplier_bills.record_payment', 'suppliers.view',
                'suppliers.create', 'suppliers.update', 'warehouses.view',
            ],
            UserRole::ShopManager->value => [
                'journal_entries.view', 'products.view', 'shops.view', 'shop_stock.view',
                'shop_stock.update', 'stock_transfers.view', 'stock_transfers.receive',
                'warehouses.view',
            ],
            UserRole::Cashier->value => [],
        ];
    }
}