<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Business Configuration</p>
        <h1 class="mt-3 text-4xl font-semibold text-ink">Business settings</h1>
        <p class="mt-3 max-w-2xl text-sm text-muted">Manage business identity, tax behavior, numbering, access, and deployment branding.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach (['general' => 'General', 'numbering' => 'Numbering', 'permissions' => 'Roles & Permissions', 'branding' => 'Branding', 'system' => 'System', 'updates' => 'Updates'] as $key => $label)
            <button type="button" wire:click="$set('tab', '{{ $key }}')" class="{{ $tab === $key ? 'btn-primary' : 'btn-secondary' }} btn-size-sm">
                {{ $label }}
                @if ($key === 'updates' && $this->updateAvailable)
                    <span class="ml-1.5 inline-flex h-2 w-2 rounded-full bg-rust"></span>
                @endif
            </button>
        @endforeach
    </div>

    @if ($tab === 'general')
        <form wire:submit="saveGeneral" class="rounded-ui border border-line bg-surface p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-medium text-muted">Legal name</label><input wire:model.live="settings.legal_name" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Commercial Registration</label><input wire:model.live="settings.cr_number" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">VAT number</label><input wire:model.live="settings.vat_number" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Email</label><input wire:model.live="settings.email" type="email" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Phone</label><input wire:model.live="settings.phone" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">VAT rate (%)</label><input wire:model.live="settings.vat_rate" type="number" step="0.01" min="0" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Currency code</label><input wire:model.live="settings.currency_code" type="text" maxlength="3" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Currency symbol</label><input wire:model.live="settings.currency_symbol" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Quantity decimals</label><input wire:model.live="settings.quantity_decimal_places" type="number" min="0" max="6" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Price decimals</label><input wire:model.live="settings.price_decimal_places" type="number" min="0" max="4" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">{{ __('Date format') }}</label><input wire:model.live="settings.date_format" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">{{ __('Default language') }}</label><select wire:model.live="settings.default_locale" class="ui-select w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none"><option value="en">{{ __('English') }}</option><option value="ar">{{ __('Arabic') }}</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">{{ __('Legal name (Arabic)') }}</label><input wire:model.live="settings.legal_name_ar" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-muted">Address</label><textarea wire:model.live="settings.address" rows="3" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none"></textarea></div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-muted">{{ __('Address (Arabic)') }}</label><textarea wire:model.live="settings.address_ar" rows="3" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none"></textarea></div>
            </div>
            <label class="mt-5 flex items-center gap-3 text-sm text-muted"><input wire:model="settings.vat_enabled" type="checkbox" class="h-4 w-4 rounded border-line bg-panel text-brass" /><span>VAT is enabled for sales and supplier bills</span></label>
            <button type="submit" class="btn-primary mt-8">Save general settings</button>
        </form>
    @endif

    @if ($tab === 'numbering')
        <form wire:submit="saveNumbering" class="rounded-ui border border-line bg-surface p-6">
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($formats as $key => $format)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-muted">{{ $format['label'] }}</label>
                        <input wire:model.live="formats.{{ $key }}.prefix" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-muted">Numbering does not reset automatically; counters continue forever unless explicitly changed by a future migration.</p>
            <button type="submit" class="btn-primary mt-8">Save numbering</button>
        </form>
    @endif

    @if ($tab === 'permissions')
        <div class="space-y-6">
            <form wire:submit="saveRolePermissions" class="rounded-ui border border-line bg-surface p-6">
                <h2 class="text-lg font-semibold text-ink">Role permissions</h2>
                <div class="mt-5 overflow-hidden rounded-ui border border-line table-baseline">
                    <table class="min-w-full text-start text-xs ui-table">
                        <thead>
                            <tr>
                                <th class="px-3 py-3">Permission</th>
                                @foreach ($this->roles as $role)
                                    <th class="px-3 py-3">{{ str($role)->replace('_', ' ')->title() }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-ink">
                            @foreach ($this->permissions as $permission)
                                <tr>
                                    <td class="px-3 py-2 font-medium figure-mono">{{ $permission->key }}</td>
                                    @foreach ($this->roles as $role)
                                        <td class="px-3 py-2"><input wire:model="rolePermissions.{{ $role }}.{{ $permission->key }}" type="checkbox" class="h-4 w-4 rounded border-line bg-panel text-brass" /></td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn-primary mt-6">Save role permissions</button>
            </form>

            <form wire:submit="saveUserOverrides" class="rounded-ui border border-line bg-surface p-6">
                <h2 class="text-lg font-semibold text-ink">User overrides</h2>
                <select wire:model.live="selectedUserId" class="ui-select mt-4 w-full max-w-xl rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none">
                    @foreach ($this->users as $userOption)<option value="{{ $userOption->id }}">{{ $userOption->email }} ({{ $userOption->role?->value }})</option>@endforeach
                </select>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach ($this->permissions as $permission)
                        <label class="grid grid-cols-[1fr_10rem] items-center gap-3 rounded-ui border border-line bg-panel px-4 py-3 text-sm text-muted"><span class="figure-mono">{{ $permission->key }}</span><select wire:model="userOverrides.{{ $permission->key }}" class="ui-select rounded-ui border border-line bg-surface px-3 py-2 text-ink outline-none"><option value="inherit">Inherit</option><option value="grant">Grant</option><option value="revoke">Revoke</option></select></label>
                    @endforeach
                </div>
                <button type="submit" class="btn-primary mt-6">Save user overrides</button>
            </form>
        </div>
    @endif

    @if ($tab === 'branding')
        <form wire:submit="saveBranding" class="rounded-ui border border-line bg-surface p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-medium text-muted">Application name</label><input wire:model.live="settings.application_name" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Application short name</label><input wire:model.live="settings.application_short_name" type="text" maxlength="32" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Theme</label><select wire:model.live="settings.theme" class="ui-select w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none">@foreach ($this->themePresets as $key => $theme)<option value="{{ $key }}">{{ $theme['name'] }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Brand website</label><input wire:model.live="settings.brand_website" type="url" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>

                <div><label class="mb-2 block text-sm font-medium text-muted">Primary color override</label><input wire:model.live="settings.brand_primary_color" type="text" placeholder="#b8872f" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Accent color override</label><input wire:model.live="settings.brand_accent_color" type="text" placeholder="#24745a" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Background color override</label><input wire:model.live="settings.brand_background_color" type="text" placeholder="#0c0a09" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Surface color override</label><input wire:model.live="settings.brand_surface_color" type="text" placeholder="#1c1917" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>

                <div><label class="mb-2 block text-sm font-medium text-muted">Login title</label><input wire:model.live="settings.login_title" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Login subtitle</label><input wire:model.live="settings.login_subtitle" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>

                <div><label class="mb-2 block text-sm font-medium text-muted">Header brand text</label><input wire:model.live="settings.header_brand_text" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Header brand subtext</label><input wire:model.live="settings.header_brand_subtext" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>

                <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-muted">Footer powered-by text</label><input wire:model.live="settings.footer_powered_by_text" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div class="md:col-span-2"><label class="flex items-center gap-3 text-sm text-muted"><input wire:model="settings.footer_show_powered_by" type="checkbox" class="h-4 w-4 rounded border-line bg-panel text-brass" /><span>Show powered by footer branding</span></label></div>

                <div><label class="mb-2 block text-sm font-medium text-muted">Email branding header (placeholder)</label><input wire:model.live="settings.email_branding_header" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Email branding footer (placeholder)</label><input wire:model.live="settings.email_branding_footer" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>

                <div><label class="mb-2 block text-sm font-medium text-muted">PDF branding header (placeholder)</label><input wire:model.live="settings.pdf_branding_header" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">PDF branding footer (placeholder)</label><input wire:model.live="settings.pdf_branding_footer" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">PDF watermark text (placeholder)</label><input wire:model.live="settings.pdf_watermark_text" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>

                <div><label class="mb-2 block text-sm font-medium text-muted">Invoice footer text</label><input wire:model.live="settings.invoice_footer_text" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">{{ __('Invoice footer text (Arabic)') }}</label><input wire:model.live="settings.invoice_footer_text_ar" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Mail from name</label><input wire:model.live="settings.mail_from_name" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Mail from address</label><input wire:model.live="settings.mail_from_address" type="email" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Logo</label><input wire:model="logoUpload" type="file" accept="image/*" class="w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none" />@if ($logoUpload)<img src="{{ $logoUpload->temporaryUrl() }}" alt="Logo preview" class="mt-3 max-h-24 rounded-ui border border-line bg-surface p-2" />@endif</div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Favicon</label><input wire:model="faviconUpload" type="file" accept="image/*" class="w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none" />@if ($faviconUpload)<img src="{{ $faviconUpload->temporaryUrl() }}" alt="Favicon preview" class="mt-3 h-16 w-16 rounded-ui border border-line bg-surface p-2" />@endif</div>
                <div><label class="mb-2 block text-sm font-medium text-muted">Touch icon</label><input wire:model="touchIconUpload" type="file" accept="image/*" class="w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none" />@if ($touchIconUpload)<img src="{{ $touchIconUpload->temporaryUrl() }}" alt="Touch icon preview" class="mt-3 h-16 w-16 rounded-ui border border-line bg-surface p-2" />@endif</div>
            </div>
            <button type="submit" class="btn-primary mt-8">Save branding</button>
        </form>
    @endif

    @if ($tab === 'updates')
        <div class="rounded-ui border border-line bg-surface p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-ink">{{ __('ERP updates') }}</h2>
                    <p class="mt-2 max-w-2xl text-sm text-muted">{{ __('Check for new SyntekPro ERP releases published on GitHub.') }}</p>
                </div>
                <button
                    type="button"
                    wire:click="checkForUpdates"
                    wire:loading.attr="disabled"
                    wire:target="checkForUpdates"
                    class="btn-secondary btn-size-sm"
                    data-check-for-updates
                >
                    <span wire:loading.remove wire:target="checkForUpdates">{{ __('Check for updates') }}</span>
                    <span wire:loading wire:target="checkForUpdates">{{ __('Checking…') }}</span>
                </button>
            </div>

            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Installed version') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink figure-mono" data-installed-version>{{ $updateInfo['installed_version'] }}</dd>
                </div>
                <div class="rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Latest available version') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink figure-mono" data-latest-version>
                        {{ $updateInfo['latest_version'] ?? __('Unknown') }}
                        @if ($updateInfo['is_available'])
                            <span class="ml-2 inline-flex items-center rounded-full bg-rust/10 px-2 py-0.5 text-xs font-medium text-rust">{{ __('Update available') }}</span>
                        @else
                            <span class="ml-2 inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-500">{{ __('Up to date') }}</span>
                        @endif
                    </dd>
                </div>
                <div class="rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Release date') }}</dt>
                    <dd class="mt-1 text-sm text-ink">{{ $updateInfo['released_at'] ? \Carbon\Carbon::parse($updateInfo['released_at'])->format('Y-m-d H:i') : '-' }}</dd>
                </div>
                <div class="rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Last update check') }}</dt>
                    <dd class="mt-1 text-sm text-ink" data-last-check>{{ $updateInfo['checked_at'] ? \Carbon\Carbon::parse($updateInfo['checked_at'])->format('Y-m-d H:i') : '-' }}</dd>
                </div>
            </dl>

            @if ($updateInfo['release_name'])
                <div class="mt-6 rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Release name') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">{{ $updateInfo['release_name'] }}</dd>
                </div>
            @endif

            @if ($updateInfo['release_notes'])
                <div class="mt-6 rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Release notes') }}</dt>
                    <dd class="mt-2 whitespace-pre-wrap text-sm text-ink">{{ $updateInfo['release_notes'] }}</dd>
                </div>
            @endif

            @if ($this->hasUpdateStatus)
                <div
                    class="mt-6 rounded-ui border border-line bg-panel p-4"
                    @if ($this->shouldPollUpdateStatus)
                        wire:poll.5s="pollUpdateStatus"
                    @endif
                >
                    @if ($updateInProgress)
                        <div class="text-sm text-ink">
                            <div class="flex items-center gap-3">
                                <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-brass border-t-transparent"></span>
                                <p class="font-semibold">{{ __('Background update in progress') }}</p>
                            </div>
                            <p class="mt-2">{{ $updateJob['message'] ?? __('SyntekPro ERP is being updated to :version.', ['version' => $updateTargetVersion]) }}</p>
                            <p class="mt-1 text-muted">{{ __('You can leave this page and return later. This screen will reconnect and continue polling the update job status.') }}</p>
                        </div>
                    @elseif ($this->updateCompleted)
                        <div class="text-sm text-emerald-600">
                            <p class="font-semibold">{{ __('Update complete') }}</p>
                            <p class="mt-1">{{ __('SyntekPro ERP has been updated to :version.', ['version' => $updateInfo['installed_version']]) }}</p>
                            <button type="button" wire:click="clearUpdateStatus" class="btn-secondary btn-size-sm mt-3">{{ __('Dismiss') }}</button>
                        </div>
                    @elseif ($updateResult !== null && ($updateResult['status'] ?? null) === 'success')
                        <div class="text-sm text-emerald-600">
                            <p class="font-semibold">{{ __('Update succeeded') }}</p>
                            <p class="mt-1">{{ $updateResult['message'] ?? '' }}</p>
                            <p class="mt-1">{{ __('Waiting for the application to report the new version…') }}</p>
                        </div>
                    @elseif ($updateError)
                        <div class="text-sm text-rust">
                            <p class="font-semibold">{{ __('Update failed') }}</p>
                            <p class="mt-1">{{ $updateError }}</p>
                            <button type="button" wire:click="clearUpdateStatus" class="btn-secondary btn-size-sm mt-3">{{ __('Dismiss') }}</button>
                        </div>
                    @elseif ($confirmingUpdate)
                        <div class="text-sm text-ink">
                            <p class="font-semibold">{{ __('Confirm update') }}</p>
                            <p class="mt-1 text-muted">{{ __('Are you sure you want to update SyntekPro ERP from :installed to :target? The update runs in the background, maintenance mode will be enabled during deployment, and both the database and public assets are backed up automatically for rollback.', ['installed' => $updateInfo['installed_version'], 'target' => $updateInfo['latest_version']]) }}</p>
                            <div class="mt-3 flex flex-wrap gap-3">
                                <button type="button" wire:click="cancelUpdate" class="btn-secondary btn-size-sm">{{ __('Cancel') }}</button>
                                <button type="button" wire:click="startUpdate" wire:loading.attr="disabled" wire:target="startUpdate" class="btn-primary btn-size-sm">{{ __('Yes, update now') }}</button>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="text-sm text-ink">
                                <p class="font-semibold">{{ __('A new version is available') }}</p>
                                <p class="mt-1 text-muted">{{ __('Update SyntekPro ERP to :version with one click.', ['version' => $updateInfo['latest_version']]) }}</p>
                            </div>
                            <button type="button" wire:click="promptUpdate" class="btn-primary btn-size-sm" data-update-now>{{ __('Update Now') }}</button>
                        </div>
                    @endif
                </div>
            @endif

            @if (! $updateInfo['latest_version'] && ! $updateJob)
                <div class="mt-6 rounded-ui border border-line bg-panel p-4 text-sm text-muted">
                    {{ __('No release information has been retrieved yet. Click "Check for updates" to query GitHub.') }}
                </div>
            @endif
        </div>
    @endif

    @if ($tab === 'system')
        <div class="rounded-ui border border-line bg-surface p-6">
            <h2 class="text-lg font-semibold text-ink">{{ __('Product information') }}</h2>
            <p class="mt-2 max-w-2xl text-sm text-muted">{{ __('Current SyntekPro ERP version and product details.') }}</p>

            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Product name') }}</dt>
                    <dd class="mt-1 text-sm text-ink">{{ config('syntek.name') }}</dd>
                </div>
                <div class="rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Version') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink figure-mono" data-product-version>{{ config('syntek.version') }}</dd>
                </div>
                <div class="rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Release channel') }}</dt>
                    <dd class="mt-1 text-sm text-ink">{{ config('syntek.release_channel') }}</dd>
                </div>
                <div class="rounded-ui border border-line bg-panel p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Support') }}</dt>
                    <dd class="mt-1 text-sm text-ink"><a href="mailto:{{ config('syntek.support_email') }}" class="text-link hover:underline">{{ config('syntek.support_email') }}</a></dd>
                </div>
            </dl>
        </div>
    @endif
</section>