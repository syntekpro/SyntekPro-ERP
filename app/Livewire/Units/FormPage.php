<?php

namespace App\Livewire\Units;

use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    public ?Unit $unit = null;

    public string $code = '';

    public string $name = '';

    public bool $is_active = true;

    protected bool $codeWasManuallyEdited = false;

    public function mount(?Unit $unit = null): void
    {
        abort_unless($this->canManageSettings(), 403);

        $this->unit = $unit?->exists ? $unit : null;

        if ($this->unit) {
            $this->code = $this->unit->code;
            $this->name = $this->unit->name;
            $this->is_active = $this->unit->is_active;
            $this->codeWasManuallyEdited = true;
        }
    }

    public function updatedName(string $value): void
    {
        if (! $this->codeWasManuallyEdited) {
            $this->code = Str::upper(Str::slug($value, '-'));
        }
    }

    public function updatedCode(): void
    {
        $this->codeWasManuallyEdited = true;
    }

    public function save()
    {
        abort_unless($this->canManageSettings(), 403);

        $validated = $this->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('units', 'code')->ignore($this->unit?->id)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['code'] = Str::upper($validated['code']);

        if ($this->unit) {
            $this->unit->update($validated);
            session()->flash('status', 'Unit updated.');
        } else {
            Unit::query()->create($validated);
            session()->flash('status', 'Unit created.');
        }

        return redirect()->route('units.index');
    }

    public function render()
    {
        return view('livewire.units.form-page');
    }

    protected function canManageSettings(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('settings.manage');
    }
}