@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Catalog transfer</p>
                <h1 class="mt-3 text-4xl font-semibold text-ink">Import products</h1>
                <p class="mt-3 max-w-2xl text-sm text-muted">Upload the same CSV or XLSX schema produced by export. Rows are validated first and committed only after confirmation.</p>
            </div>
            <x-button variant="secondary" :href="route('products.index')"><x-lucide-arrow-left class="h-4 w-4" /> Back to catalog</x-button>
        </div>

        <form method="POST" action="{{ route('products.import.preview') }}" enctype="multipart/form-data" class="rounded-ui border border-line bg-surface p-6 shadow-sm">
            @csrf
            <label class="block rounded-ui border border-dashed border-line bg-panel p-8 text-center">
                <x-lucide-file-spreadsheet class="mx-auto h-10 w-10 text-brass" />
                <span class="mt-4 block text-sm font-semibold text-ink">Choose CSV or XLSX catalog file</span>
                <input name="catalog_file" type="file" accept=".csv,.txt,.xlsx,.xls" class="mt-4 text-sm text-muted" />
            </label>
            @error('catalog_file') <p class="mt-3 text-sm text-rust">{{ $message }}</p> @enderror
            <div class="mt-5 flex justify-end">
                <x-button type="submit"><x-lucide-search-check class="h-4 w-4" /> Preview import</x-button>
            </div>
        </form>

        @if ($preview)
            <div class="rounded-ui border border-line bg-surface p-6 shadow-sm">
                <div class="grid gap-3 md:grid-cols-3">
                    <div class="rounded-ui border border-line bg-panel p-4"><p class="text-xs uppercase tracking-[0.18em] text-subtle">Create</p><p class="mt-2 text-2xl font-semibold figure-mono text-ink">{{ $preview['created'] }}</p></div>
                    <div class="rounded-ui border border-line bg-panel p-4"><p class="text-xs uppercase tracking-[0.18em] text-subtle">Update</p><p class="mt-2 text-2xl font-semibold figure-mono text-ink">{{ $preview['updated'] }}</p></div>
                    <div class="rounded-ui border border-line bg-panel p-4"><p class="text-xs uppercase tracking-[0.18em] text-subtle">Rejected</p><p class="mt-2 text-2xl font-semibold figure-mono text-ink">{{ $preview['rejected'] }}</p></div>
                </div>

                @if (($preview['errors'] ?? []) !== [])
                    <div class="mt-5 rounded-ui border border-rust/40 bg-panel p-4 text-sm text-ink">
                        <h2 class="font-semibold text-rust">Row errors</h2>
                        <ul class="mt-3 space-y-1">
                            @foreach ($preview['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-5 overflow-hidden rounded-ui border border-line table-baseline">
                    <table class="min-w-full text-start text-sm">
                        <thead class="text-muted"><tr><th class="px-4 py-3">Row</th><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Result</th></tr></thead>
                        <tbody class="divide-y divide-line text-ink">
                            @foreach ($preview['rows'] as $row)
                                <tr>
                                    <td class="px-4 py-3 figure-mono">{{ $row['row'] }}</td>
                                    <td class="px-4 py-3 figure-mono">{{ $row['sku'] }}</td>
                                    <td class="px-4 py-3">{{ ucfirst($row['action']) }}</td>
                                    <td class="px-4 py-3">
                                        <x-status-badge :tone="$row['valid'] ? 'success' : 'danger'">{{ $row['valid'] ? 'Ready' : 'Rejected' }}</x-status-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('products.import.confirm') }}" class="mt-5 flex justify-end">
                    @csrf
                    <x-button type="submit"><x-lucide-check class="h-4 w-4" /> Confirm valid rows</x-button>
                </form>
            </div>
        @endif
    </section>
@endsection
