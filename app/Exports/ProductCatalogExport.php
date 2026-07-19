<?php

namespace App\Exports;

use App\Services\Products\ProductCatalogSpreadsheetService;
use Maatwebsite\Excel\Concerns\FromArray;

class ProductCatalogExport implements FromArray
{
    public function __construct(protected ProductCatalogSpreadsheetService $catalog)
    {
    }

    public function array(): array
    {
        return $this->catalog->exportRows();
    }
}
