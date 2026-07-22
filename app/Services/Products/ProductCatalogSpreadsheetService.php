<?php

namespace App\Services\Products;

use App\Models\PriceCategory;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductUnitConversion;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductCatalogSpreadsheetService
{
    public function headings(): array
    {
        $headings = [
            'SKU/code',
            'name',
            'description',
            'base unit',
            'price',
            'purchase price',
            'VAT rate',
            'is_excise_applicable',
            'excise_rate',
            'is_active',
            'stock_min',
            'stock_max',
        ];

        Unit::query()
            ->where('is_active', true)
            ->where('code', '!=', 'PCS')
            ->orderBy('code')
            ->pluck('code')
            ->each(fn (string $code) => $headings[] = "Unit: {$code} - factor");

        PriceCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->each(fn (string $name) => $headings[] = "Price: {$name}");

        return $headings;
    }

    public function exportRows(): array
    {
        $headings = $this->headings();
        $rows = [$headings];

        Product::query()
            ->with(['baseUnit', 'unitConversions.unit', 'prices.priceCategory'])
            ->orderBy('sku')
            ->get()
            ->each(function (Product $product) use (&$rows, $headings): void {
                $row = array_fill_keys($headings, '');
                $row['SKU/code'] = $product->sku;
                $row['name'] = $product->name;
                $row['description'] = (string) ($product->description ?? '');
                $row['base unit'] = $product->baseUnit?->code ?? 'PCS';
                $row['price'] = number_format((float) $product->price, 2, '.', '');
                $row['purchase price'] = number_format((float) $product->cost_price, 2, '.', '');
                $row['VAT rate'] = number_format((float) $product->vat_rate, 2, '.', '');
                $row['is_excise_applicable'] = $product->is_excise_applicable ? '1' : '0';
                $row['excise_rate'] = $product->excise_rate !== null ? number_format((float) $product->excise_rate, 2, '.', '') : '';
                $row['is_active'] = $product->is_active ? '1' : '0';
                $row['stock_min'] = $product->stock_min !== null ? number_format((float) $product->stock_min, 3, '.', '') : '';
                $row['stock_max'] = $product->stock_max !== null ? number_format((float) $product->stock_max, 3, '.', '') : '';

                foreach ($product->unitConversions as $conversion) {
                    $heading = 'Unit: '.$conversion->unit?->code.' - factor';
                    if (array_key_exists($heading, $row)) {
                        $row[$heading] = number_format((float) $conversion->conversion_factor, 6, '.', '');
                    }
                }

                foreach ($product->prices as $price) {
                    $heading = 'Price: '.$price->priceCategory?->name;
                    if (array_key_exists($heading, $row)) {
                        $row[$heading] = number_format((float) $price->price, 2, '.', '');
                    }
                }

                $rows[] = array_values($row);
            });

        return $rows;
    }

    public function exportCsv(): string
    {
        $handle = fopen('php://temp', 'w+');

        foreach ($this->exportRows() as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return (string) $csv;
    }

    public function rowsFromUpload(UploadedFile $file): array
    {
        if (in_array(strtolower($file->getClientOriginalExtension()), ['xlsx', 'xls'], true)) {
            return $this->normalizeSpreadsheetRows(Excel::toArray(null, $file)[0] ?? []);
        }

        $handle = fopen($file->getRealPath(), 'r');
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $this->normalizeSpreadsheetRows($rows);
    }

    public function preview(array $rows): array
    {
        if ($rows === []) {
            return ['rows' => [], 'created' => 0, 'updated' => 0, 'rejected' => 1, 'errors' => ['row 1: empty import file']];
        }

        $headings = array_map(fn ($heading) => trim((string) $heading), array_shift($rows));
        $previewRows = [];
        $errors = [];
        $created = 0;
        $updated = 0;
        $rejected = 0;

        foreach ($rows as $offset => $rowValues) {
            $rowNumber = $offset + 2;
            if ($this->rowIsBlank($rowValues)) {
                continue;
            }

            $row = array_combine($headings, array_pad($rowValues, count($headings), '')) ?: [];
            $result = $this->validateRow($row, $rowNumber);
            $previewRows[] = $result;

            if ($result['valid']) {
                $result['action'] === 'create' ? $created++ : $updated++;
            } else {
                $rejected++;
                array_push($errors, ...$result['errors']);
            }
        }

        return compact('previewRows', 'created', 'updated', 'rejected', 'errors') + ['rows' => $previewRows];
    }

    public function commit(array $preview): array
    {
        $committed = 0;

        DB::transaction(function () use ($preview, &$committed): void {
            foreach ($preview['rows'] ?? [] as $row) {
                if (! ($row['valid'] ?? false)) {
                    continue;
                }

                $payload = $row['payload'];
                $product = Product::query()->updateOrCreate(['sku' => $payload['sku']], $payload['product']);
                $this->syncUnitConversions($product, $payload['unit_conversions']);
                $this->syncCategoryPrices($product, $payload['category_prices']);
                $committed++;
            }
        });

        return ['committed' => $committed];
    }

    protected function normalizeSpreadsheetRows(array $rows): array
    {
        return array_map(fn (array $row) => array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row), $rows);
    }

    protected function validateRow(array $row, int $rowNumber): array
    {
        $errors = [];
        $sku = trim((string) ($row['SKU/code'] ?? ''));
        $name = trim((string) ($row['name'] ?? ''));
        $baseUnitCode = Str::upper(trim((string) ($row['base unit'] ?? '')));
        $baseUnitId = $baseUnitCode !== '' ? Unit::query()->where('code', $baseUnitCode)->value('id') : null;

        if ($sku === '') {
            $errors[] = "row {$rowNumber}: SKU/code is required";
        }

        if ($name === '') {
            $errors[] = "row {$rowNumber}: name is required";
        }

        if (! $baseUnitId) {
            $errors[] = "row {$rowNumber}: unknown unit code '{$baseUnitCode}'";
        }

        foreach (['price', 'purchase price', 'VAT rate', 'excise_rate', 'stock_min', 'stock_max'] as $numericColumn) {
            $value = $row[$numericColumn] ?? '';
            if ($value !== '' && (! is_numeric($value) || (float) $value < 0)) {
                $errors[] = "row {$rowNumber}: {$numericColumn} must be a non-negative number";
            }
        }

        $unitConversions = [];
        $categoryPrices = [];

        foreach ($row as $heading => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            if (Str::startsWith($heading, 'Unit: ')) {
                $unitCode = Str::upper(trim(Str::before(Str::after($heading, 'Unit: '), ' - factor')));
                $unitId = Unit::query()->where('code', $unitCode)->value('id');

                if (! $unitId) {
                    $errors[] = "row {$rowNumber}: unknown unit code '{$unitCode}'";
                } elseif (! is_numeric($value) || (float) $value <= 0) {
                    $errors[] = "row {$rowNumber}: {$heading} must be greater than zero";
                } elseif ((int) $unitId !== (int) $baseUnitId) {
                    $unitConversions[(int) $unitId] = (float) $value;
                }
            }

            if (Str::startsWith($heading, 'Price: ')) {
                $categoryName = trim(Str::after($heading, 'Price: '));
                $categoryId = PriceCategory::query()->where('name', $categoryName)->value('id');

                if (! $categoryId) {
                    $errors[] = "row {$rowNumber}: unknown price category '{$categoryName}'";
                } elseif (! is_numeric($value) || (float) $value < 0) {
                    $errors[] = "row {$rowNumber}: {$heading} must be a non-negative number";
                } else {
                    $categoryPrices[(int) $categoryId] = (float) $value;
                }
            }
        }

        $product = $sku !== '' ? Product::query()->where('sku', $sku)->first() : null;
        $payload = [
            'sku' => $sku,
            'product' => [
                'name' => $name,
                'description' => ($row['description'] ?? '') === '' ? null : (string) $row['description'],
                'base_unit_id' => $baseUnitId,
                'price' => ($row['price'] ?? '') === '' ? 0 : (float) $row['price'],
                'cost_price' => ($row['purchase price'] ?? '') === '' ? 0 : (float) $row['purchase price'],
                'vat_rate' => ($row['VAT rate'] ?? '') === '' ? 0 : (float) $row['VAT rate'],
                'is_excise_applicable' => $this->booleanValue($row['is_excise_applicable'] ?? false),
                'excise_rate' => ($row['excise_rate'] ?? '') === '' ? null : (float) $row['excise_rate'],
                'is_active' => $this->booleanValue($row['is_active'] ?? true),
                'stock_min' => ($row['stock_min'] ?? '') === '' ? null : (float) $row['stock_min'],
                'stock_max' => ($row['stock_max'] ?? '') === '' ? null : (float) $row['stock_max'],
            ],
            'unit_conversions' => $unitConversions,
            'category_prices' => $categoryPrices,
        ];

        if ($payload['product']['stock_min'] !== null && $payload['product']['stock_max'] !== null && $payload['product']['stock_max'] < $payload['product']['stock_min']) {
            $errors[] = "row {$rowNumber}: stock_max must be greater than or equal to stock_min";
        }

        return [
            'row' => $rowNumber,
            'sku' => $sku,
            'action' => $product ? 'update' : 'create',
            'valid' => $errors === [],
            'errors' => $errors,
            'payload' => $payload,
        ];
    }

    protected function syncUnitConversions(Product $product, array $conversions): void
    {
        $product->unitConversions()->delete();

        foreach ($conversions as $unitId => $factor) {
            ProductUnitConversion::query()->create([
                'product_id' => $product->id,
                'unit_id' => $unitId,
                'conversion_factor' => $factor,
            ]);
        }
    }

    protected function syncCategoryPrices(Product $product, array $prices): void
    {
        ProductPrice::query()->where('product_id', $product->id)->delete();

        foreach ($prices as $categoryId => $price) {
            ProductPrice::query()->create([
                'product_id' => $product->id,
                'price_category_id' => $categoryId,
                'price' => $price,
            ]);
        }
    }

    protected function booleanValue(mixed $value): bool
    {
        return in_array(Str::lower((string) $value), ['1', 'true', 'yes', 'y', 'active'], true);
    }

    protected function rowIsBlank(array $row): bool
    {
        return collect($row)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }
}
