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
        return $this->isSuperAdmin($user) || $this->isShopManager($user);
    }

    public function view(User $user, StockTransfer $transfer): bool
    {
        return $this->isSuperAdmin($user) || $this->managesShopId($user, $transfer->destination_shop_id);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function markInTransit(User $user, StockTransfer $transfer): bool
    {
        return $this->isSuperAdmin($user) && $transfer->status === StockTransferStatus::Pending;
    }

    public function receive(User $user, StockTransfer $transfer): bool
    {
        return ($this->isSuperAdmin($user) || $this->managesShopId($user, $transfer->destination_shop_id))
            && $transfer->status === StockTransferStatus::InTransit;
    }
}