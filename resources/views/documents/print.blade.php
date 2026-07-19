<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $document['type'] }} {{ $document['document_number'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { margin: 14mm; }
        body { background: white; color: #111827; }
        .print-sheet { max-width: 210mm; margin: 0 auto; padding: 24px; font-family: 'IBM Plex Sans', sans-serif; }
        .receipt { max-width: 80mm; padding: 8px; font-size: 11px; }
        .receipt table { font-size: 10px; }
        @media print {
            .no-print { display: none !important; }
            .print-sheet { max-width: none; padding: 0; }
            .receipt { width: 80mm; }
            @page receipt { size: 80mm auto; margin: 4mm; }
        }
    </style>
</head>
<body>
    <main class="print-sheet {{ $format === 'receipt' ? 'receipt' : '' }}">
        <div class="no-print mb-6 flex justify-end gap-2">
            <button onclick="window.print()" class="btn-primary"><x-lucide-printer class="h-4 w-4" /> Print</button>
        </div>

        <header class="flex items-start justify-between gap-6 border-b border-gray-300 pb-6">
            <div>
                <img src="{{ $logoUrl }}" alt="Logo" class="h-14 w-auto object-contain" />
                <h1 class="mt-4 text-xl font-semibold">{{ $businessSettings->legal_name ?: config('app.name') }}</h1>
                <p class="mt-1 text-sm text-gray-600">VAT {{ $businessSettings->vat_number ?: 'Not configured' }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs uppercase tracking-[0.18em] text-gray-500">{{ $document['type'] }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ $document['document_number'] }}</p>
                <p class="mt-1 text-sm text-gray-600">{{ optional($document['date'])->format('Y-m-d') }}</p>
            </div>
        </header>

        <section class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Counterparty</p>
                <p class="mt-2 font-semibold">{{ $document['counterparty_name'] ?: 'N/A' }}</p>
            </div>
        </section>

        <table class="mt-6 w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 text-gray-600">
                    <th class="py-2">Item</th>
                    <th class="py-2 text-right">Qty</th>
                    <th class="py-2 text-right">Unit price</th>
                    <th class="py-2 text-right">VAT</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document['lines'] as $line)
                    <tr class="border-b border-gray-200">
                        <td class="py-2">{{ $line['description'] }}</td>
                        <td class="py-2 text-right">{{ number_format($line['quantity'], 3) }} {{ $line['unit'] }}</td>
                        <td class="py-2 text-right">SAR {{ number_format($line['unit_price'], 2) }}</td>
                        <td class="py-2 text-right">{{ number_format($line['vat_rate'], 2) }}%</td>
                        <td class="py-2 text-right">SAR {{ number_format($line['line_total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <section class="ml-auto mt-6 w-full max-w-xs space-y-2 text-sm">
            <div class="flex justify-between"><span>Subtotal</span><span>SAR {{ number_format($document['subtotal'], 2) }}</span></div>
            <div class="flex justify-between"><span>VAT</span><span>SAR {{ number_format($document['vat'], 2) }}</span></div>
            <div class="flex justify-between border-t border-gray-300 pt-2 text-lg font-semibold"><span>Total</span><span>SAR {{ number_format($document['total'], 2) }}</span></div>
        </section>

        @if ($businessSettings->invoice_footer_text)
            <footer class="mt-10 border-t border-gray-300 pt-4 text-center text-sm text-gray-600">{{ $businessSettings->invoice_footer_text }}</footer>
        @endif
    </main>
</body>
</html>
