<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Warehouse::class);

        return view('warehouses.index');
    }

    public function create(): View
    {
        $this->authorize('create', Warehouse::class);

        return view('warehouses.create');
    }

    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('update', $warehouse);

        return view('warehouses.edit', compact('warehouse'));
    }
}