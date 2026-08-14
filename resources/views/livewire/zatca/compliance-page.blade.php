<section class="space-y-6">
    <div class="flex items-start gap-3">
        <x-icon-tile color="ledger" size="lg">
            <x-lucide-shield-check class="h-7 w-7" />
        </x-icon-tile>
        <div>
            <p class="text-xs font-medium text-ledger">Back office module</p>
            <h1 class="mt-1 text-3xl font-semibold text-ink">{{ __('ZATCA Compliance') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-muted">{{ __('Phase 2 e-invoicing setup: seller address, CSR generation, and certificate status.') }}</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-ui border border-line bg-panel px-4 py-3 text-sm text-ink">{{ session('status') }}</div>
    @endif

    <x-card surface="surface">
        <x-slot:header>
            <h2 class="text-lg font-semibold text-ink">{{ __('1. Onboarding checklist') }}</h2>
        </x-slot:header>
        <ol class="list-decimal space-y-1 ps-5 text-sm text-muted">
            <li>{{ __('Fill in the structured seller address below (required for the invoice XML).') }}</li>
            <li>{{ __('Generate a CSR here using your real VAT number and legal business name (set those in Settings first).') }}</li>
            <li>{{ __('Register on the ZATCA Fatoora Portal with your ERAD credentials.') }}</li>
            <li>{{ __('Request an OTP, then submit the downloaded CSR to the Compliance CSID endpoint using that OTP.') }}</li>
            <li>{{ __('Pass the compliance sandbox checks, then request your Production CSID.') }}</li>
        </ol>
    </x-card>

    <x-card surface="surface">
        <x-slot:header>
            <h2 class="text-lg font-semibold text-ink">{{ __('2. Seller address') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('Required by the UBL invoice schema.') }}</p>
        </x-slot:header>

        <form wire:submit="saveAddress" class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-ink">{{ __('Street name') }}</label>
                <x-input type="text" wire:model="street_name" />
                @error('street_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink">{{ __('Building number') }}</label>
                <x-input type="text" wire:model="building_number" />
                @error('building_number') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink">{{ __('Additional number') }} <span class="text-subtle">({{ __('optional') }})</span></label>
                <x-input type="text" wire:model="additional_number" />
            </div>
            <div>
                <label class="text-sm font-medium text-ink">{{ __('District') }}</label>
                <x-input type="text" wire:model="district" />
                @error('district') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink">{{ __('City') }}</label>
                <x-input type="text" wire:model="city" />
                @error('city') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink">{{ __('Postal code') }}</label>
                <x-input type="text" wire:model="postal_code" />
                @error('postal_code') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink">{{ __('Country code') }}</label>
                <x-input type="text" wire:model="country_code" maxlength="2" />
                @error('country_code') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <x-button type="submit">{{ __('Save address') }}</x-button>
            </div>
        </form>
    </x-card>

    <x-card surface="surface">
        <x-slot:header>
            <h2 class="text-lg font-semibold text-ink">{{ __('3. Generate a CSR') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('Uses your VAT number and legal name from Settings automatically.') }}</p>
        </x-slot:header>

        <form wire:submit="generateCsr" class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-ink">{{ __('Common name') }}</label>
                <x-input type="text" wire:model="common_name" placeholder="SyntekPro ERP" />
                @error('common_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink">{{ __('Branch / unit name') }}</label>
                <x-input type="text" wire:model="organization_unit_name" placeholder="Main Branch" />
                @error('organization_unit_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <x-button type="submit" wire:loading.attr="disabled" wire:target="generateCsr">
                    <span wire:loading.remove wire:target="generateCsr">{{ __('Generate CSR') }}</span>
                    <span wire:loading wire:target="generateCsr">{{ __('Generating…') }}</span>
                </x-button>
            </div>
        </form>
    </x-card>

    <x-card surface="surface">
        <x-slot:header>
            <h2 class="text-lg font-semibold text-ink">{{ __('Certificates') }}</h2>
        </x-slot:header>

        @if ($certificates->isEmpty())
            <p class="text-sm text-muted">{{ __('No certificates generated yet.') }}</p>
        @else
            <x-table>
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-start">{{ __('Type') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('Environment') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('Created') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('Status') }}</th>
                        <th class="px-4 py-2 text-end">{{ __('Files') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($certificates as $certificate)
                        <tr class="border-t border-line">
                            <td class="px-4 py-2 capitalize">{{ $certificate->csid_type }}</td>
                            <td class="px-4 py-2 capitalize">{{ $certificate->environment }}</td>
                            <td class="px-4 py-2">{{ $certificate->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-2">
                                @if ($certificate->revoked_at)
                                    <x-badge tone="danger">{{ __('Revoked') }}</x-badge>
                                @elseif ($certificate->binary_security_token)
                                    <x-badge tone="success">{{ __('Issued') }}</x-badge>
                                @else
                                    <x-badge tone="warning">{{ __('CSR only - not yet submitted to ZATCA') }}</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-end">
                                <div class="flex justify-end gap-2">
                                    @if ($certificate->csr)
                                        <x-button size="sm" variant="secondary" href="{{ route('zatca-compliance.csr.download', $certificate) }}">{{ __('CSR') }}</x-button>
                                    @endif
                                    @if ($certificate->private_key_encrypted)
                                        <x-button size="sm" variant="ghost" href="{{ route('zatca-compliance.private-key.download', $certificate) }}">{{ __('Private key') }}</x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
            <p class="mt-3 text-xs text-subtle">{{ __('Keep the private key secret - anyone with it can sign invoices as your business. Only download it over a trusted connection.') }}</p>
        @endif
    </x-card>
</section>
