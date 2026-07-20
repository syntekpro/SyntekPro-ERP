<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Business Configuration</p>
        <h1 class="mt-3 text-4xl font-semibold text-white">Business settings</h1>
        <p class="mt-3 max-w-2xl text-sm text-stone-300">Manage business identity, tax behavior, numbering, access, and deployment branding.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach (['general' => 'General', 'numbering' => 'Numbering', 'permissions' => 'Roles & Permissions', 'branding' => 'Branding'] as $key => $label)
            <button type="button" wire:click="$set('tab', '{{ $key }}')" class="rounded-2xl px-4 py-2 text-sm font-semibold transition {{ $tab === $key ? 'bg-amber-400 text-stone-950' : 'border border-white/10 text-stone-100 hover:bg-white/10' }}">{{ $label }}</button>
        @endforeach
    </div>

    @if ($tab === 'general')
        <form wire:submit="saveGeneral" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Legal name</label><input wire:model.live="settings.legal_name" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Commercial Registration</label><input wire:model.live="settings.cr_number" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">VAT number</label><input wire:model.live="settings.vat_number" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Email</label><input wire:model.live="settings.email" type="email" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Phone</label><input wire:model.live="settings.phone" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">VAT rate (%)</label><input wire:model.live="settings.vat_rate" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Currency code</label><input wire:model.live="settings.currency_code" type="text" maxlength="3" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Currency symbol</label><input wire:model.live="settings.currency_symbol" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Quantity decimals</label><input wire:model.live="settings.quantity_decimal_places" type="number" min="0" max="6" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Price decimals</label><input wire:model.live="settings.price_decimal_places" type="number" min="0" max="4" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">{{ __('Date format') }}</label><input wire:model.live="settings.date_format" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">{{ __('Default language') }}</label><select wire:model.live="settings.default_locale" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none"><option value="en">{{ __('English') }}</option><option value="ar">{{ __('Arabic') }}</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">{{ __('Legal name (Arabic)') }}</label><input wire:model.live="settings.legal_name_ar" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-stone-200">Address</label><textarea wire:model.live="settings.address" rows="3" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none"></textarea></div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-stone-200">{{ __('Address (Arabic)') }}</label><textarea wire:model.live="settings.address_ar" rows="3" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none"></textarea></div>
            </div>
            <label class="mt-5 flex items-center gap-3 text-sm text-stone-300"><input wire:model="settings.vat_enabled" type="checkbox" class="h-4 w-4 rounded border-white/10 bg-stone-900 text-amber-400" /><span>VAT is enabled for sales and supplier bills</span></label>
            <button type="submit" class="mt-8 rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save general settings</button>
        </form>
    @endif

    @if ($tab === 'numbering')
        <form wire:submit="saveNumbering" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($formats as $key => $format)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-200">{{ $format['label'] }}</label>
                        <input wire:model.live="formats.{{ $key }}.prefix" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-stone-400">Numbering does not reset automatically; counters continue forever unless explicitly changed by a future migration.</p>
            <button type="submit" class="mt-8 rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save numbering</button>
        </form>
    @endif

    @if ($tab === 'permissions')
        <div class="space-y-6">
            <form wire:submit="saveRolePermissions" class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Role permissions</h2>
                <div class="mt-5 overflow-hidden rounded-2xl border border-white/10">
                    <table class="min-w-full divide-y divide-white/10 text-start text-xs">
                        <thead class="bg-stone-900 text-stone-300"><tr><th class="px-3 py-3">Permission</th>@foreach ($this->roles as $role)<th class="px-3 py-3">{{ str($role)->replace('_', ' ')->title() }}</th>@endforeach</tr></thead>
                        <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                            @foreach ($this->permissions as $permission)
                                <tr><td class="px-3 py-2 font-medium">{{ $permission->key }}</td>@foreach ($this->roles as $role)<td class="px-3 py-2"><input wire:model="rolePermissions.{{ $role }}.{{ $permission->key }}" type="checkbox" class="h-4 w-4 rounded border-white/10 bg-stone-900 text-amber-400" /></td>@endforeach</tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="mt-6 rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save role permissions</button>
            </form>

            <form wire:submit="saveUserOverrides" class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">User overrides</h2>
                <select wire:model.live="selectedUserId" class="mt-4 w-full max-w-xl rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                    @foreach ($this->users as $userOption)<option value="{{ $userOption->id }}">{{ $userOption->email }} ({{ $userOption->role?->value }})</option>@endforeach
                </select>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach ($this->permissions as $permission)
                        <label class="grid grid-cols-[1fr_10rem] items-center gap-3 rounded-2xl border border-white/10 bg-stone-950/60 px-4 py-3 text-sm text-stone-200"><span>{{ $permission->key }}</span><select wire:model="userOverrides.{{ $permission->key }}" class="rounded-xl border border-white/10 bg-stone-900 px-3 py-2 text-stone-100 outline-none"><option value="inherit">Inherit</option><option value="grant">Grant</option><option value="revoke">Revoke</option></select></label>
                    @endforeach
                </div>
                <button type="submit" class="mt-6 rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save user overrides</button>
            </form>
        </div>
    @endif

    @if ($tab === 'branding')
        <form wire:submit="saveBranding" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Theme</label><select wire:model.live="settings.theme" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">@foreach ($this->themePresets as $key => $theme)<option value="{{ $key }}">{{ $theme['name'] }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Invoice footer text</label><input wire:model.live="settings.invoice_footer_text" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">{{ __('Invoice footer text (Arabic)') }}</label><input wire:model.live="settings.invoice_footer_text_ar" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Mail from name</label><input wire:model.live="settings.mail_from_name" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Mail from address</label><input wire:model.live="settings.mail_from_address" type="email" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" /></div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Logo</label><input wire:model="logoUpload" type="file" accept="image/*" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none" />@if ($logoUpload)<img src="{{ $logoUpload->temporaryUrl() }}" alt="Logo preview" class="mt-3 max-h-24 rounded-xl border border-white/10 bg-white p-2" />@endif</div>
                <div><label class="mb-2 block text-sm font-medium text-stone-200">Favicon</label><input wire:model="faviconUpload" type="file" accept="image/*" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none" />@if ($faviconUpload)<img src="{{ $faviconUpload->temporaryUrl() }}" alt="Favicon preview" class="mt-3 h-16 w-16 rounded-xl border border-white/10 bg-white p-2" />@endif</div>
            </div>
            <button type="submit" class="mt-8 rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save branding</button>
        </form>
    @endif
</section>