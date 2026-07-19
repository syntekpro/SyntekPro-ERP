<?php

namespace App\Http\Controllers;

use App\Exports\ProductCatalogExport;
use App\Models\Product;
use App\Services\Products\ProductCatalogSpreadsheetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ProductCatalogTransferController extends Controller
{
    public function import(): View
    {
        $this->authorize('create', Product::class);

        return view('products.import', [
            'preview' => session('product_import_preview'),
        ]);
    }

    public function preview(Request $request, ProductCatalogSpreadsheetService $catalog): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'catalog_file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        $preview = $catalog->preview($catalog->rowsFromUpload($validated['catalog_file']));
        session(['product_import_preview' => $preview]);

        return redirect()->route('products.import');
    }

    public function confirm(ProductCatalogSpreadsheetService $catalog): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $preview = session('product_import_preview');
        abort_unless(is_array($preview), 422);

        $result = $catalog->commit($preview);
        session()->forget('product_import_preview');

        return redirect()->route('products.index')->with('status', $result['committed'].' product rows imported.');
    }

    public function export(Request $request, ProductCatalogSpreadsheetService $catalog)
    {
        $this->authorize('viewAny', Product::class);

        $format = strtolower((string) $request->query('format', 'xlsx'));

        if ($format === 'csv') {
            return new Response($catalog->exportCsv(), 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="product-catalog.csv"',
            ]);
        }

        return Excel::download(new ProductCatalogExport($catalog), 'product-catalog.xlsx');
    }
}
