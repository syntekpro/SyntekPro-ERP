<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $document['type'] }} {{ $document['document_number'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { margin: 14mm; }
        body { background: white; color: #111827; }
        .print-sheet { max-width: 210mm; margin: 0 auto; padding: 24px; font-family: 'IBM Plex Sans', sans-serif; }
        html[lang='ar'] .print-sheet { font-family: 'IBM Plex Sans Arabic', 'IBM Plex Sans', sans-serif; }
        .receipt { max-width: 80mm; padding: 8px; font-size: 11px; }
        .receipt table { font-size: 10px; }
        .ltr-content { direction: ltr; unicode-bidi: isolate; display: inline-block; }
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
                <h1 class="mt-4 text-xl font-semibold">{{ app()->getLocale() === 'ar' ? ($businessSettings->legal_name_ar ?: $businessSettings->legal_name ?: config('app.name')) : ($businessSettings->legal_name ?: config('app.name')) }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ __('VAT') }} <span class="ltr-content">{{ $businessSettings->vat_number ?: __('Not configured') }}</span></p>
            </div>
            <div class="text-end">
                <p class="text-xs uppercase tracking-[0.18em] text-gray-500">{{ $document['type'] }}</p>
                <p class="mt-2 text-2xl font-semibold ltr-content">{{ $document['document_number'] }}</p>
                <p class="mt-1 text-sm text-gray-600 ltr-content">{{ optional($document['date'])->translatedFormat('Y-m-d') }}</p>
            </div>
        </header>

        <section class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-gray-500">{{ __('Counterparty') }}</p>
                <p class="mt-2 font-semibold">{{ $document['counterparty_name'] ?: __('N/A') }}</p>
            </div>
        </section>

        <table class="mt-6 w-full border-collapse text-start text-sm">
            <thead>
                <tr class="border-b border-gray-300 text-gray-600">
                    <th class="py-2">{{ __('Item') }}</th>
                    <th class="py-2 text-end">{{ __('Qty') }}</th>
                    <th class="py-2 text-end">{{ __('Unit price') }}</th>
                    <th class="py-2 text-end">{{ __('VAT') }}</th>
                    <th class="py-2 text-end">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document['lines'] as $line)
                    <tr class="border-b border-gray-200">
                        <td class="py-2">{{ $line['description'] }}</td>
                        <td class="py-2 text-end"><span class="ltr-content">{{ number_format($line['quantity'], 3) }}</span> {{ $line['unit'] }}</td>
                        <td class="py-2 text-end"><span class="ltr-content">SAR {{ number_format($line['unit_price'], 2) }}</span></td>
                        <td class="py-2 text-end"><span class="ltr-content">{{ number_format($line['vat_rate'], 2) }}%</span></td>
                        <td class="py-2 text-end"><span class="ltr-content">SAR {{ number_format($line['line_total'], 2) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <section class="ms-auto mt-6 w-full max-w-xs space-y-2 text-sm">
            <div class="flex justify-between"><span>{{ __('Subtotal') }}</span><span class="ltr-content">SAR {{ number_format($document['subtotal'], 2) }}</span></div>
            <div class="flex justify-between"><span>{{ __('VAT') }}</span><span class="ltr-content">SAR {{ number_format($document['vat'], 2) }}</span></div>
            <div class="flex justify-between border-t border-gray-300 pt-2 text-lg font-semibold"><span>{{ __('Total') }}</span><span class="ltr-content">SAR {{ number_format($document['total'], 2) }}</span></div>
        </section>

        @php
            $footerText = app()->getLocale() === 'ar'
                ? ($businessSettings->invoice_footer_text_ar ?: $businessSettings->invoice_footer_text)
                : $businessSettings->invoice_footer_text;
        @endphp
        @if ($footerText)
            <footer class="mt-10 border-t border-gray-300 pt-4 text-center text-sm text-gray-600">{{ $footerText }}</footer>
        @endif
    </main>
</body>
</html>
