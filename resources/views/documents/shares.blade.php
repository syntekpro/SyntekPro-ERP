@extends('layouts.hub')

@section('title', 'Document Shares')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Documents</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Shared document links</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Review and revoke expiring public document links.</p>
        </div>

        @if (session('status'))
            <div class="rounded-ui border border-line bg-panel px-4 py-3 text-sm text-ink">{{ session('status') }}</div>
        @endif

        <div class="overflow-hidden rounded-ui border border-line bg-surface table-baseline">
            <table class="min-w-full text-left text-sm">
                <thead class="text-muted"><tr><th class="px-4 py-3">Document</th><th class="px-4 py-3">Expires</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($shares as $share)
                        <tr>
                            <td class="px-4 py-3 figure-mono">{{ $share->document_type }} #{{ $share->document_id }}</td>
                            <td class="px-4 py-3">{{ $share->expires_at->toDateString() }}</td>
                            <td class="px-4 py-3"><x-status-badge :tone="$share->isViewable() ? 'success' : 'danger'">{{ $share->isViewable() ? 'Active' : 'Unavailable' }}</x-status-badge></td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <x-button variant="secondary" :href="route('documents.shared', $share->token)"><x-lucide-external-link class="h-4 w-4" /> Open</x-button>
                                    @if ($share->revoked_at === null)
                                        <form method="POST" action="{{ route('document-shares.revoke', $share) }}">
                                            @csrf
                                            <x-button type="submit" variant="danger"><x-lucide-ban class="h-4 w-4" /> Revoke</x-button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-muted">No share links have been created.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $shares->links() }}
    </section>
@endsection
