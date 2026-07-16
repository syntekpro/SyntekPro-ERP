<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', StockTransfer::class);

        return view('stock-transfers.index');
    }

    public function create(): View
    {
        $this->authorize('create', StockTransfer::class);

        return view('stock-transfers.create');
    }
}