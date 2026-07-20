<?php

namespace App\Livewire\Settings;

use App\Enums\UserRole;
use App\Models\BusinessSetting;
use App\Models\DocumentNumberFormat;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\Settings\BusinessSettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class SettingsPage extends Component
{
    use WithFileUploads;

    public string $tab = 'general';
    public array $settings = [];
    public array $formats = [];
    public array $rolePermissions = [];
    public array $userOverrides = [];
    public ?int $selectedUserId = null;
    public $logoUpload;
    public $faviconUpload;

    public function mount(BusinessSettingsService $settingsService): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        abort_unless($user?->hasPermission('settings.manage'), 403);

        $businessSettings = $settingsService->current();
        $this->settings = $businessSettings->only([
            'legal_name', 'cr_number', 'vat_number', 'address', 'phone', 'email', 'vat_enabled', 'vat_rate',
            'currency_code', 'currency_symbol', 'quantity_decimal_places', 'price_decimal_places', 'date_format',
            'default_locale', 'theme', 'invoice_footer_text', 'mail_from_name', 'mail_from_address',
            'legal_name_ar', 'address_ar', 'invoice_footer_text_ar',
        ]);
        $this->settings['vat_enabled'] = (bool) $this->settings['vat_enabled'];
        $this->settings['vat_rate'] = number_format((float) $this->settings['vat_rate'], 2, '.', '');

        $this->loadFormats();
        $this->loadRolePermissions();
        $this->selectedUserId = User::query()->orderBy('email')->value('id');
        $this->loadUserOverrides();
    }

    public function saveGeneral(): void
    {
        $validated = $this->validate([
            'settings.legal_name' => ['nullable', 'string', 'max:255'],
            'settings.cr_number' => ['nullable', 'string', 'max:64'],
            'settings.vat_number' => ['nullable', 'string', 'max:32'],
            'settings.address' => ['nullable', 'string', 'max:2000'],
            'settings.phone' => ['nullable', 'string', 'max:40'],
            'settings.email' => ['nullable', 'email', 'max:255'],
            'settings.vat_enabled' => ['boolean'],
            'settings.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'settings.currency_code' => ['required', 'string', 'size:3'],
            'settings.currency_symbol' => ['required', 'string', 'max:12'],
            'settings.quantity_decimal_places' => ['required', 'integer', 'min:0', 'max:6'],
            'settings.price_decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
            'settings.date_format' => ['required', 'string', 'max:40'],
            'settings.default_locale' => ['required', Rule::in(['en', 'ar'])],
            'settings.legal_name_ar' => ['nullable', 'string', 'max:255'],
            'settings.address_ar' => ['nullable', 'string', 'max:2000'],
            'settings.invoice_footer_text_ar' => ['nullable', 'string', 'max:2000'],
        ]);

        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update($this->emptyStringsToNull($validated['settings']));
        session()->flash('status', 'Business settings updated.');
    }

    public function saveNumbering(): void
    {
        $this->validate([
            'formats.*.prefix' => ['required', 'string', 'max:20'],
        ]);

        foreach ($this->formats as $key => $format) {
            DocumentNumberFormat::query()->updateOrCreate(['key' => $key], [
                'label' => $format['label'],
                'prefix' => $format['prefix'],
                'reset_frequency' => 'never',
                'next_reset' => null,
            ]);
        }

        session()->flash('status', 'Document numbering formats updated.');
        $this->loadFormats();
    }

    public function saveRolePermissions(): void
    {
        DB::transaction(function (): void {
            foreach ($this->rolePermissions as $role => $permissions) {
                RolePermission::query()->where('role', $role)->delete();

                foreach ($permissions as $permissionKey => $enabled) {
                    if ($enabled) {
                        $permissionId = Permission::query()->where('key', $permissionKey)->value('id');
                        RolePermission::query()->create(['role' => $role, 'permission_id' => $permissionId]);
                    }
                }
            }
        });

        session()->flash('status', 'Role permissions updated.');
    }

    public function updatedSelectedUserId(): void
    {
        $this->loadUserOverrides();
    }

    public function saveUserOverrides(): void
    {
        if ($this->selectedUserId === null) {
            return;
        }

        UserPermission::query()->where('user_id', $this->selectedUserId)->delete();

        foreach ($this->userOverrides as $permissionKey => $effect) {
            if (in_array($effect, ['grant', 'revoke'], true)) {
                $permissionId = Permission::query()->where('key', $permissionKey)->value('id');
                UserPermission::query()->create([
                    'user_id' => $this->selectedUserId,
                    'permission_id' => $permissionId,
                    'effect' => $effect,
                ]);
            }
        }

        session()->flash('status', 'User permission overrides updated.');
    }

    public function saveBranding(BusinessSettingsService $settingsService): void
    {
        $rules = [
            'settings.theme' => ['required', Rule::in(array_keys($settingsService->themePresets()))],
            'settings.invoice_footer_text' => ['nullable', 'string', 'max:2000'],
            'settings.invoice_footer_text_ar' => ['nullable', 'string', 'max:2000'],
            'settings.mail_from_name' => ['nullable', 'string', 'max:255'],
            'settings.mail_from_address' => ['nullable', 'email', 'max:255'],
            'logoUpload' => ['nullable', 'image', 'max:2048'],
            'faviconUpload' => ['nullable', 'image', 'max:1024'],
        ];

        $validated = $this->validate($rules);
        $payload = $this->emptyStringsToNull($validated['settings']);

        if ($this->logoUpload instanceof TemporaryUploadedFile) {
            $payload['logo_path'] = $this->logoUpload->store('branding', 'public');
        }

        if ($this->faviconUpload instanceof TemporaryUploadedFile) {
            $payload['favicon_path'] = $this->faviconUpload->store('branding', 'public');
        }

        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update($payload);
        $this->logoUpload = null;
        $this->faviconUpload = null;
        session()->flash('status', 'Branding settings updated.');
    }

    public function getPermissionsProperty()
    {
        return Permission::query()->orderBy('key')->get();
    }

    public function getUsersProperty()
    {
        return User::query()->orderBy('email')->get(['id', 'email', 'role']);
    }

    public function getRolesProperty(): array
    {
        return array_map(fn (UserRole $role) => $role->value, UserRole::cases());
    }

    public function getThemePresetsProperty(): array
    {
        return app(BusinessSettingsService::class)->themePresets();
    }

    public function render()
    {
        return view('livewire.settings.settings-page');
    }

    protected function loadFormats(): void
    {
        $this->formats = DocumentNumberFormat::query()->orderBy('label')->get()->mapWithKeys(fn (DocumentNumberFormat $format) => [
            $format->key => ['label' => $format->label, 'prefix' => $format->prefix],
        ])->all();
    }

    protected function loadRolePermissions(): void
    {
        $keys = Permission::query()->pluck('key')->all();

        foreach ($this->roles as $role) {
            $enabled = RolePermission::query()
                ->where('role', $role)
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->pluck('permissions.key')
                ->all();

            foreach ($keys as $key) {
                $this->rolePermissions[$role][$key] = in_array($key, $enabled, true);
            }
        }
    }

    protected function loadUserOverrides(): void
    {
        $this->userOverrides = Permission::query()->orderBy('key')->pluck('key')->mapWithKeys(fn (string $key) => [$key => 'inherit'])->all();

        if ($this->selectedUserId === null) {
            return;
        }

        $overrides = UserPermission::query()
            ->where('user_id', $this->selectedUserId)
            ->join('permissions', 'permissions.id', '=', 'user_permissions.permission_id')
            ->pluck('user_permissions.effect', 'permissions.key');

        foreach ($overrides as $key => $effect) {
            $this->userOverrides[$key] = $effect;
        }
    }

    protected function emptyStringsToNull(array $values): array
    {
        return collect($values)->map(fn ($value) => $value === '' ? null : $value)->all();
    }
}