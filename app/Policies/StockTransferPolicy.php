<?php

namespace App\Policies;

use App\Enums\StockTransferStatus;
use App\Models\StockTransfer;
use App\Models\User;
use App\Policies\Concerns\AuthorizesShopResources;

class StockTransferPolicy
{
    use AuthorizesShopResources;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('stock_transfers.view');
    }

    public function view(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermission('stock_transfers.view')
            && ($user->role?->value !== 'shop_manager' || $this->managesShopId($user, $transfer->destination_shop_id));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('stock_transfers.create');
    }

    public function markInTransit(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermission('stock_transfers.mark_in_transit') && $transfer->status === StockTransferStatus::Pending;
    }

    public function receive(User $user, StockTransfer $transfer): bool
    {
        return $user->hasPermission('stock_transfers.receive')
            && ($user->role?->value !== 'shop_manager' || $this->managesShopId($user, $transfer->destination_shop_id))
            && $transfer->status === StockTransferStatus::InTransit;
    }
}