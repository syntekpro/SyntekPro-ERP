<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'shop_id',
        'is_active',
        'theme_mode',
        'navigation_state',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'navigation_state' => 'array',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isShopManager(): bool
    {
        return $this->role === UserRole::ShopManager;
    }

    public function isAccountant(): bool
    {
        return $this->role?->value === 'accountant';
    }

    public function isCashier(): bool
    {
        return $this->role === UserRole::Cashier;
    }

    public function hasPermission(string $key): bool
    {
        $permission = Permission::query()->where('key', $key)->first();

        if ($permission === null) {
            return false;
        }

        $override = DB::table('user_permissions')
            ->where('user_id', $this->id)
            ->where('permission_id', $permission->id)
            ->value('effect');

        if ($override === 'grant') {
            return true;
        }

        if ($override === 'revoke') {
            return false;
        }

        return DB::table('role_permissions')
            ->where('role', $this->role?->value)
            ->where('permission_id', $permission->id)
            ->exists();
    }
}
