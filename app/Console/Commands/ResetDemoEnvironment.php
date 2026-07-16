<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetDemoEnvironment extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Reset and reseed the demo environment with fictional data.';

    public function handle(): int
    {
        if (! config('app.demo_mode')) {
            $this->error('Demo reset is blocked because APP_DEMO_MODE is disabled.');

            return self::FAILURE;
        }

        $databaseName = strtolower((string) config('database.connections.'.config('database.default').'.database'));

        if ($databaseName === '' || ! str_contains($databaseName, 'demo')) {
            $this->error('Demo reset is blocked because the active database name does not contain "demo".');

            return self::FAILURE;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'sale_items',
            'sales',
            'stock_transfer_items',
            'stock_transfers',
            'shop_stock',
            'warehouse_stock',
            'users',
            'products',
            'shops',
            'warehouses',
        ] as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $warehouse = Warehouse::query()->create([
            'name' => 'Demo Central Warehouse',
            'code' => 'DEMO-WH-01',
            'is_active' => true,
        ]);

        $shop = Shop::query()->create([
            'name' => 'Demo Riyadh Express',
            'legal_name' => 'Demo Riyadh Express Trading Co.',
            'vat_registration_number' => '300000000000003',
            'slug' => 'demo-riyadh',
            'is_active' => true,
        ]);

        $products = collect([
            [
                'name' => 'Fictional Premium Dates 500g',
                'sku' => 'DEMO-DATES-500',
                'barcode' => '6299000000001',
                'price' => 32.50,
                'cost_price' => 20.00,
                'vat_rate' => 15.00,
            ],
            [
                'name' => 'Fictional Arabic Coffee 250g',
                'sku' => 'DEMO-COFFEE-250',
                'barcode' => '6299000000002',
                'price' => 21.00,
                'cost_price' => 12.50,
                'vat_rate' => 15.00,
            ],
            [
                'name' => 'Fictional Saffron Blend 20g',
                'sku' => 'DEMO-SAFFRON-20',
                'barcode' => '6299000000003',
                'price' => 48.00,
                'cost_price' => 31.00,
                'vat_rate' => 15.00,
            ],
        ])->map(fn (array $row) => Product::query()->create($row + ['is_active' => true]));

        foreach ($products as $product) {
            WarehouseStock::query()->create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'quantity' => 300,
            ]);

            ShopStock::query()->forAllShops()->create([
                'shop_id' => $shop->id,
                'product_id' => $product->id,
                'quantity' => 80,
            ]);
        }

        $password = (string) env('DEMO_DEFAULT_PASSWORD', 'password');

        User::query()->create([
            'name' => 'Demo Super Admin',
            'email' => (string) env('DEMO_SUPER_ADMIN_EMAIL', 'demo.admin@syntekpro.com'),
            'password' => Hash::make($password),
            'role' => UserRole::SuperAdmin,
            'shop_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Demo Shop Manager',
            'email' => (string) env('DEMO_SHOP_MANAGER_EMAIL', 'demo.manager@syntekpro.com'),
            'password' => Hash::make($password),
            'role' => UserRole::ShopManager,
            'shop_id' => $shop->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Demo Cashier',
            'email' => (string) env('DEMO_CASHIER_EMAIL', 'demo.cashier@syntekpro.com'),
            'password' => Hash::make($password),
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->info('Demo environment reset complete with fictional shop, users, products, and stock.');

        return self::SUCCESS;
    }
}
