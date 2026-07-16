<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => env('SEED_SUPER_ADMIN_EMAIL', 'development@syntekpro.com'),
        ], [
            'name' => 'SyntekPro Super Admin',
            'password' => Hash::make(env('SEED_SUPER_ADMIN_PASSWORD', 'password')),
            'role' => UserRole::SuperAdmin,
            'shop_id' => null,
            'email_verified_at' => now(),
        ]);
    }
}
