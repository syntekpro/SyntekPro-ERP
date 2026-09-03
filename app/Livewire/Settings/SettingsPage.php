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
use App\Services\Updates\UpdateManager;
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
    public $touchIconUpload;

    public array $updateInfo = [];
    public bool $updateCheckInProgress = false;

    public bool $confirmingUpdate = false;
    public bool $updateInProgress = false;
    public ?string $updateTargetVersion = null;
    public ?array $updateResult = null;
    public ?string $updateError = null;
    public ?array $updateJob = null;
    public ?string $dismissedUpdateJobId = null;

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
            'application_name', 'application_short_name',
            'brand_primary_color', 'brand_accent_color', 'brand_background_color', 'brand_surface_color',
            'login_title', 'login_subtitle',
            'header_brand_text', 'header_brand_subtext',
            'footer_show_powered_by', 'footer_powered_by_text', 'brand_website',
            'email_branding_header', 'email_branding_footer',
            'pdf_branding_header', 'pdf_branding_footer', 'pdf_watermark_text',
        ]);
        $this->settings['vat_enabled'] = (bool) $this->settings['vat_enabled'];
        $this->settings['footer_show_powered_by'] = (bool) ($this->settings['footer_show_powered_by'] ?? true);
        $this->settings['vat_rate'] = number_format((float) $this->settings['vat_rate'], 2, '.', '');

        $this->loadFormats();
        $this->loadRolePermissions();
        $this->selectedUserId = User::query()->orderBy('email')->value('id');
        $this->loadUserOverrides();
        $this->loadUpdateInfo();
    }

    public function checkForUpdates(UpdateManager $manager): void
    {
        $this->updateCheckInProgress = true;
        $manager->latest(forceRefresh: true);
        $this->loadUpdateInfo();
        $this->updateCheckInProgress = false;
    }

    public function promptUpdate(): void
    {
        $this->confirmingUpdate = true;
        $this->updateResult = null;
        $this->updateError = null;
    }

    public function cancelUpdate(): void
    {
        $this->confirmingUpdate = false;
    }

    public function startUpdate(UpdateManager $manager): void
    {
        $version = $this->updateInfo['latest_version'] ?? null;

        if (! is_string($version) || $version === '') {
            $this->updateError = __('No target version is available.');
            $this->confirmingUpdate = false;

            return;
        }

        $this->confirmingUpdate = false;
        $this->updateInProgress = true;
        $this->updateTargetVersion = $version;
        $this->updateResult = null;
        $this->updateError = null;
        $this->dismissedUpdateJobId = null;

        try {
            $response = $manager->requestUpdate($version);

            if ($response === null) {
                $this->updateError = __('The update agent did not respond. Please check that the updater service is running.');

                return;
            }

            $data = $response['data'] ?? [];

            if (($response['status'] ?? 0) >= 200 && ($response['status'] ?? 0) < 300 && ($data['ok'] ?? false)) {
                $this->syncUpdateJob(is_array($data['job'] ?? null) ? $data['job'] : null);
            } else {
                $message = $this->sanitizeError($data['error'] ?? $data['rollback_error'] ?? __('The update failed.'));

                if (! empty($data['rolled_back_to'])) {
                    $message .= ' ' . __('The system was rolled back to version :version.', ['version' => $data['rolled_back_to']]);
                }

                $this->updateError = $message;
            }
        } catch (\Throwable $exception) {
            $this->updateError = $this->sanitizeError($exception->getMessage());
        } finally {
            $this->updateInProgress = false;
            $this->loadUpdateInfo();
        }
    }

    public function pollUpdateStatus(): void
    {
        if (! $this->shouldPollUpdateStatus && $this->updateJob === null) {
            return;
        }

        $this->loadUpdateInfo();
    }

    public function clearUpdateStatus(UpdateManager $manager): void
    {
        $this->dismissedUpdateJobId = $this->updateJob['id'] ?? null;
        $response = $manager->clearUpdateStatus();

        if ($response !== null && ($response['status'] ?? 500) >= 400) {
            $this->dismissedUpdateJobId = null;

            return;
        }

        $this->updateJob = null;
        $this->updateInProgress = false;
        $this->updateResult = null;
        $this->updateError = null;
        $this->updateTargetVersion = null;
        $this->loadUpdateInfo();
    }

    public function getUpdateCompletedProperty(): bool
    {
        return ($this->updateJob['status'] ?? null) === 'success'
            && $this->updateInfo['installed_version'] === ($this->updateJob['version'] ?? $this->updateTargetVersion ?? '');
    }

    public function getShouldPollUpdateStatusProperty(): bool
    {
        $status = $this->updateJob['status'] ?? null;

        return in_array($status, ['pending', 'running'], true)
            || ($status === 'success' && ! $this->updateCompleted);
    }

    public function getHasUpdateStatusProperty(): bool
    {
        return $this->updateAvailable
            || $this->confirmingUpdate
            || $this->updateInProgress
            || $this->updateResult !== null
            || $this->updateError !== null
            || $this->updateJob !== null;
    }

    protected function sanitizeError(mixed $message): string
    {
        $text = is_string($message) ? $message : __('An unexpected error occurred.');

        $patterns = [
            '/token=[^\s&]*/i' => 'token=***',
            '/Bearer\s+[^\s]*/i' => 'Bearer ***',
            '/password=[^\s&]*/i' => 'password=***',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        return $text;
    }


    public function getUpdateAvailableProperty(): bool
    {
        return $this->updateInfo['is_available'] ?? false;
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
            'settings.application_name' => ['nullable', 'string', 'max:255'],
            'settings.application_short_name' => ['nullable', 'string', 'max:32'],
            'settings.brand_primary_color' => ['nullable', 'string', 'max:20'],
            'settings.brand_accent_color' => ['nullable', 'string', 'max:20'],
            'settings.brand_background_color' => ['nullable', 'string', 'max:20'],
            'settings.brand_surface_color' => ['nullable', 'string', 'max:20'],
            'settings.login_title' => ['nullable', 'string', 'max:255'],
            'settings.login_subtitle' => ['nullable', 'string', 'max:255'],
            'settings.header_brand_text' => ['nullable', 'string', 'max:255'],
            'settings.header_brand_subtext' => ['nullable', 'string', 'max:255'],
            'settings.footer_show_powered_by' => ['boolean'],
            'settings.footer_powered_by_text' => ['nullable', 'string', 'max:255'],
            'settings.brand_website' => ['nullable', 'string', 'max:255'],
            'settings.email_branding_header' => ['nullable', 'string', 'max:255'],
            'settings.email_branding_footer' => ['nullable', 'string', 'max:2000'],
            'settings.pdf_branding_header' => ['nullable', 'string', 'max:255'],
            'settings.pdf_branding_footer' => ['nullable', 'string', 'max:2000'],
            'settings.pdf_watermark_text' => ['nullable', 'string', 'max:255'],
            'logoUpload' => ['nullable', 'image', 'max:2048'],
            'faviconUpload' => ['nullable', 'image', 'max:1024'],
            'touchIconUpload' => ['nullable', 'image', 'max:1024'],
        ];

        $validated = $this->validate($rules);
        $payload = $this->emptyStringsToNull($validated['settings']);

        if ($this->logoUpload instanceof TemporaryUploadedFile) {
            $payload['logo_path'] = $this->logoUpload->store('branding', 'public');
        }

        if ($this->faviconUpload instanceof TemporaryUploadedFile) {
            $payload['favicon_path'] = $this->faviconUpload->store('branding', 'public');
        }

        if ($this->touchIconUpload instanceof TemporaryUploadedFile) {
            $payload['touch_icon_path'] = $this->touchIconUpload->store('branding', 'public');
        }

        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update($payload);
        $this->logoUpload = null;
        $this->faviconUpload = null;
        $this->touchIconUpload = null;
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

    protected function loadUpdateInfo(): void
    {
        $manager = app(UpdateManager::class);
        $latest = $manager->latest();
        $agentStatus = $manager->agentStatus();
        $job = $this->updateJob;

        if ($agentStatus !== null && ($agentStatus['status'] ?? 0) >= 200 && ($agentStatus['status'] ?? 0) < 300) {
            $job = is_array($agentStatus['data']['job'] ?? null) ? $agentStatus['data']['job'] : null;
        }

        $this->syncUpdateJob($job);

        $this->updateInfo = [
            'installed_version' => $manager->installedVersion(),
            'latest_version' => $latest?->version,
            'release_name' => $latest?->name,
            'release_notes' => $latest?->notes,
            'released_at' => $latest?->released_at?->toDateTimeString(),
            'checked_at' => $latest?->checked_at?->toDateTimeString()
                ?? $manager->lastCheckAt()?->toDateTimeString(),
            'is_available' => $manager->isUpdateAvailable($latest),
            'agent_reachable' => $agentStatus !== null && ($agentStatus['status'] ?? 0) >= 200 && ($agentStatus['status'] ?? 0) < 300,
        ];
    }

    protected function syncUpdateJob(?array $job): void
    {
        if (($job['id'] ?? null) !== null && $this->dismissedUpdateJobId === $job['id']) {
            $job = null;
        }

        $this->updateJob = $job;

        if ($job === null) {
            $this->updateInProgress = false;
            $this->updateResult = null;

            return;
        }

        $status = $job['status'] ?? null;
        $this->updateTargetVersion = $job['requested_version'] ?? $job['version'] ?? $this->updateTargetVersion;
        $this->updateInProgress = in_array($status, ['pending', 'running'], true);

        if ($this->updateInProgress) {
            $this->updateResult = [
                'status' => 'queued',
                'message' => $job['message'] ?? __('Update queued.'),
            ];
            $this->updateError = null;

            return;
        }

        if ($status === 'success') {
            $this->updateResult = [
                'status' => 'success',
                'version' => $job['version'] ?? $this->updateTargetVersion,
                'message' => $job['message'] ?? __('Update completed successfully.'),
            ];
            $this->updateError = null;

            return;
        }

        if ($status === 'failed') {
            $message = $this->sanitizeError($job['error'] ?? $job['rollback_error'] ?? __('The update failed.'));

            if (! empty($job['rolled_back_to'])) {
                $message .= ' ' . __('The system was rolled back to version :version.', ['version' => $job['rolled_back_to']]);
            }

            if (! empty($job['rollback_error'])) {
                $message .= ' ' . __('Rollback error: :message', ['message' => $this->sanitizeError($job['rollback_error'])]);
            }

            $this->updateError = $message;
            $this->updateResult = null;
        }
    }
}
