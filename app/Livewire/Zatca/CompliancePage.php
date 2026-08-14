<?php

namespace App\Livewire\Zatca;

use App\Models\ZatcaCertificate;
use App\Services\Settings\BusinessSettingsService;
use App\Services\Zatca\ZatcaCsrGenerator;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

class CompliancePage extends Component
{
    public string $street_name = '';

    public string $building_number = '';

    public string $additional_number = '';

    public string $district = '';

    public string $city = '';

    public string $postal_code = '';

    public string $country_code = 'SA';

    public string $common_name = '';

    public string $organization_unit_name = '';

    public function mount(BusinessSettingsService $settingsService): void
    {
        $this->authorizeAccess();

        $settings = $settingsService->current();

        $this->street_name = (string) ($settings->street_name ?? '');
        $this->building_number = (string) ($settings->building_number ?? '');
        $this->additional_number = (string) ($settings->additional_number ?? '');
        $this->district = (string) ($settings->district ?? '');
        $this->city = (string) ($settings->city ?? '');
        $this->postal_code = (string) ($settings->postal_code ?? '');
        $this->country_code = $settings->country_code ?: 'SA';
        $this->common_name = $settingsService->applicationName();
    }

    public function saveAddress(BusinessSettingsService $settingsService): void
    {
        $this->authorizeAccess();

        $validated = $this->validate([
            'street_name' => ['required', 'string', 'max:255'],
            'building_number' => ['required', 'string', 'max:10'],
            'additional_number' => ['nullable', 'string', 'max:10'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'country_code' => ['required', 'string', 'size:2'],
        ]);

        $settingsService->current()->update($validated);

        session()->flash('status', __('Seller address saved.'));
    }

    public function generateCsr(BusinessSettingsService $settingsService, ZatcaCsrGenerator $generator): void
    {
        $this->authorizeAccess();

        $this->validate([
            'common_name' => ['required', 'string', 'max:255'],
            'organization_unit_name' => ['required', 'string', 'max:255'],
        ]);

        $settings = $settingsService->current();

        if (empty($settings->vat_number) || empty($settings->legal_name)) {
            $this->addError('common_name', __('Set your legal business name and VAT number in Settings before generating a CSR.'));

            return;
        }

        $result = $generator->generate([
            'common_name' => $this->common_name,
            'organization_identifier' => $settings->vat_number,
            'organization_unit_name' => $this->organization_unit_name,
            'organization_name' => $settings->legal_name,
        ]);

        ZatcaCertificate::create([
            'environment' => 'sandbox',
            'csid_type' => 'compliance',
            'csr' => $result['csr'],
            'private_key_encrypted' => Crypt::encryptString($result['private_key']),
        ]);

        session()->flash('status', __('CSR generated. Download it below and submit it to the ZATCA Fatoora Portal to request your Compliance CSID.'));
    }

    public function render()
    {
        $certificates = ZatcaCertificate::query()->orderByDesc('created_at')->get();

        return view('livewire.zatca.compliance-page', compact('certificates'));
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAccountant() || $user->hasPermission('settings.manage')), 403);
    }
}
