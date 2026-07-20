@props(['type', 'id', 'allowReceipt' => false])

<div class="flex flex-wrap justify-end gap-2">
    <x-button variant="secondary" :href="route('documents.print', ['type' => $type, 'id' => $id])" target="_blank">
        <x-lucide-printer class="h-4 w-4" /> Print
    </x-button>
    @if ($allowReceipt)
        <x-button variant="secondary" :href="route('documents.print', ['type' => $type, 'id' => $id, 'format' => 'receipt'])" target="_blank">
            <x-lucide-receipt class="h-4 w-4" /> Receipt
        </x-button>
    @endif
    <form method="POST" action="{{ route('documents.share', ['type' => $type, 'id' => $id]) }}">
        @csrf
        <x-button type="submit" variant="secondary"><x-lucide-link class="h-4 w-4" /> Share Link</x-button>
    </form>
    <form method="POST" action="{{ route('documents.email', ['type' => $type, 'id' => $id]) }}" class="flex gap-2">
        @csrf
        <x-input name="email" type="email" required placeholder="Email" class="w-44 px-3 py-2 text-sm" />
        <x-button type="submit" variant="secondary" size="sm"><x-lucide-mail class="h-4 w-4" /> Email</x-button>
    </form>
</div>
