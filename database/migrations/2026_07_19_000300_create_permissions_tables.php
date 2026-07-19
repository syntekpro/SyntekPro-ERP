<?php

use App\Support\DefaultPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('role', 40);
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role', 'permission_id']);
        });

        Schema::create('user_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->string('effect', 10);
            $table->timestamps();
            $table->unique(['user_id', 'permission_id']);
        });

        foreach (DefaultPermissions::labels() as $key => $label) {
            $permissionId = DB::table('permissions')->insertGetId([
                'key' => $key,
                'label' => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (DefaultPermissions::roleMap() as $role => $keys) {
                if (in_array($key, $keys, true)) {
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
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};