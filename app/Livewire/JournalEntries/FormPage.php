<?php

namespace App\Livewire\JournalEntries;

use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Shop;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?int $shop_id = null;

    public string $entry_date = '';

    public string $reference = '';

    public string $description = '';

    /** @var array<int, array<string, mixed>> */
    public array $lines = [];

    public function mount(): void
    {
        $this->authorize('create', JournalEntry::class);

        $this->entry_date = now()->toDateString();
        $this->addLine();
        $this->addLine();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user?->isShopManager()) {
            $this->shop_id = $user->shop_id;
        }
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'account_id' => null,
            'debit' => '0.00',
            'credit' => '0.00',
            'description' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 2) {
            return;
        }

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function save(JournalEntryService $journalEntryService)
    {
        $validated = $this->validate([
            'shop_id' => ['required', 'integer', Rule::exists('shops', 'id')],
            'entry_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', Rule::exists('accounts', 'id')],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user?->isShopManager()) {
            $validated['shop_id'] = $user->shop_id;
        }

        try {
            $journalEntryService->create([
                'shop_id' => $validated['shop_id'],
                'entry_date' => $validated['entry_date'],
                'reference' => $validated['reference'] === '' ? null : $validated['reference'],
                'description' => $validated['description'] === '' ? null : $validated['description'],
                'source' => 'manual',
                'created_by' => Auth::id(),
            ], $validated['lines']);
        } catch (UnbalancedJournalEntryException $exception) {
            $this->addError('lines', $exception->getMessage());

            return null;
        }

        session()->flash('status', 'Journal entry posted.');

        return redirect()->route('journal-entries.index');
    }

    public function getShopOptionsProperty()
    {
        return Shop::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getAccountOptionsProperty()
    {
        return Account::query()->where('is_active', true)->orderBy('code')->get();
    }

    public function render()
    {
        return view('livewire.journal-entries.form-page');
    }
}
