<?php

use App\Enums\UserRole;
use App\Support\DefaultPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roles = [
            UserRole::SuperAdmin->value,
            UserRole::Accountant->value,
        ];

        foreach (DefaultPermissions::labels() as $key => $label) {
            if (! str_starts_with($key, 'cheques.')) {
                continue;
            }

            $permissionId = DB::table('permissions')->where('key', $key)->value('id');

            if ($permissionId === null) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'key' => $key,
                    'label' => $label,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($roles as $role) {
                $exists = DB::table('role_permissions')
                    ->where('role', $role)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (! $exists) {
                    DB::table('role_permissions')->insert([
                        'role' => $role,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $keys = ['cheques.view', 'cheques.record', 'cheques.clear', 'cheques.bounce'];

        $permissionIds = DB::table('permissions')
            ->whereIn('key', $keys)
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('user_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
