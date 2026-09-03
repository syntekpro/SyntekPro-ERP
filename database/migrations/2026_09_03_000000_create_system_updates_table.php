<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_updates', function (Blueprint $table): void {
            $table->id();
            $table->string('version', 32)->unique();
            $table->string('name', 255)->nullable();
            $table->longText('notes')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->boolean('is_prerelease')->default(false);
            $table->string('status', 32)->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_updates');
    }
};
