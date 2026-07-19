<?php

namespace App\Services\Documents;

use App\Models\CreditNote;
use App\Models\DebitNote;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SupplierBill;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class PrintableDocumentService
{
    public function find(string $type, int $id): Model
    {
        $class = $this->classFor($type);

        return $class::query()->with($this->relationsFor($type))->findOrFail($id);
    }

    public function classFor(string $type): string
    {
        return match ($type) {
            'sale' => Sale::class,
            'purchase-order' => PurchaseOrder::class,
            'supplier-bill' => SupplierBill::class,
            'credit-note' => CreditNote::class,
            'debit-note' => DebitNote::class,
            default => throw new InvalidArgumentException('Unsupported printable document type.'),
        };
    }

    public function data(string $type, Model $document): array
    {
        return match ($type) {
            'sale' => $this->sale($document),
            'purchase-order' => $this->purchaseOrder($document),
            'supplier-bill' => $this->supplierBill($document),
            'credit-note' => $this->creditNote($document),
            'debit-note' => $this->debitNote($document),
            default => throw new InvalidArgumentException('Unsupported printable document type.'),
        };
    }

    protected function sale(Sale $sale): array
    {
        return [
            'type' => 'Sale invoice',
            'document_number' => $sale->invoice_number ?? 'Sale #'.$sale->id,
            'date' => $sale->sold_at,
            'counterparty_name' => $sale->customer?->name ?? 'Walk-in customer',
            'subtotal' => (float) $sale->subtotal,
            'vat' => (float) $sale->vat_total,
            'total' => (float) $sale->total,
            'lines' => $sale->items->map(fn ($item) => [
                'description' => $item->product_name,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit?->code ?? 'PCS',
                'unit_price' => (float) $item->unit_price,
                'vat_rate' => (float) $item->vat_rate,
                'line_total' => (float) $item->line_total,
            ])->all(),
        ];
    }

    protected function purchaseOrder(PurchaseOrder $purchaseOrder): array
    {
        $lines = $purchaseOrder->items->map(function ($item): array {
            $net = (float) $item->quantity_ordered * (float) $item->unit_cost;
            $vat = round($net * ((float) $item->vat_rate / 100), 2);

            return [
                'description' => $item->product?->name,
                'quantity' => (float) $item->quantity_ordered,
                'unit' => $item->unit?->code ?? $item->product?->baseUnit?->code ?? 'PCS',
                'unit_price' => (float) $item->unit_cost,
                'vat_rate' => (float) $item->vat_rate,
                'line_total' => $net + $vat,
            ];
        });

        return [
            'type' => 'Purchase order',
            'document_number' => $purchaseOrder->po_number,
            'date' => $purchaseOrder->created_at,
            'counterparty_name' => $purchaseOrder->supplier?->name,
            'subtotal' => $lines->sum(fn ($line) => $line['quantity'] * $line['unit_price']),
            'vat' => $lines->sum(fn ($line) => round(($line['quantity'] * $line['unit_price']) * ($line['vat_rate'] / 100), 2)),
            'total' => $lines->sum('line_total'),
            'lines' => $lines->all(),
        ];
    }

    protected function supplierBill(SupplierBill $bill): array
    {
        return [
            'type' => 'Supplier bill',
            'document_number' => $bill->bill_number,
            'date' => $bill->bill_date,
            'counterparty_name' => $bill->supplier?->name,
            'subtotal' => (float) $bill->subtotal,
            'vat' => (float) $bill->vat_total,
            'total' => (float) $bill->total,
            'lines' => $bill->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit?->code ?? 'PCS',
                'unit_price' => (float) $item->unit_cost,
                'vat_rate' => (float) $item->vat_rate,
                'line_total' => (float) $item->gross_amount,
            ])->all(),
        ];
    }

    protected function creditNote(CreditNote $note): array
    {
        return [
            'type' => 'Credit note',
            'document_number' => $note->credit_note_number,
            'date' => $note->note_date,
            'counterparty_name' => $note->customer?->name ?? $note->sale?->customer?->name ?? 'Walk-in customer',
            'subtotal' => (float) $note->subtotal,
            'vat' => (float) $note->vat_total,
            'total' => (float) $note->total,
            'lines' => $note->items->map(fn ($item) => [
                'description' => $item->product_name,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit?->code ?? 'PCS',
                'unit_price' => (float) $item->unit_price,
                'vat_rate' => (float) $item->vat_rate,
                'line_total' => (float) $item->gross_amount,
            ])->all(),
        ];
    }

    protected function debitNote(DebitNote $note): array
    {
        return [
            'type' => 'Debit note',
            'document_number' => $note->debit_note_number,
            'date' => $note->note_date,
            'counterparty_name' => $note->supplier?->name,
            'subtotal' => (float) $note->subtotal,
            'vat' => (float) $note->vat_total,
            'total' => (float) $note->total,
            'lines' => $note->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit?->code ?? 'PCS',
                'unit_price' => (float) $item->unit_cost,
                'vat_rate' => (float) $item->vat_rate,
                'line_total' => (float) $item->gross_amount,
            ])->all(),
        ];
    }

    protected function relationsFor(string $type): array
    {
        return match ($type) {
            'sale' => ['customer', 'items.unit'],
            'purchase-order' => ['supplier', 'items.product.baseUnit', 'items.unit'],
            'supplier-bill' => ['supplier', 'items.unit'],
            'credit-note' => ['customer', 'sale.customer', 'items.unit'],
            'debit-note' => ['supplier', 'items.unit'],
            default => [],
        };
    }
}
