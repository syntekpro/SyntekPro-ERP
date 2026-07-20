@php
	$branding = app(\App\Services\Settings\BusinessSettingsService::class)->emailBrandingPlaceholders();
@endphp

<div style="font-family:'IBM Plex Sans',Arial,sans-serif;max-width:640px;margin:0 auto;padding:20px;border:1px solid #ded1bb;border-radius:12px;background:#fffaf0;color:#102033;">
	<div style="text-align:center;padding-bottom:16px;border-bottom:1px solid #ded1bb;">
		<img src="{{ $branding['logo_url'] }}" alt="Brand logo" style="max-height:44px;width:auto;margin:0 auto 10px;" />
		<p style="margin:0;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#5f6b77;">{{ $branding['header'] }}</p>
	</div>
	<p style="margin:18px 0 8px;">{{ __('View :type :number:', ['type' => $documentType, 'number' => $documentNumber]) }}</p>
	<p style="margin:0 0 20px;"><a href="{{ $url }}">{{ $url }}</a></p>
	<p style="margin:0;padding-top:16px;border-top:1px solid #ded1bb;font-size:12px;color:#5f6b77;">{{ $branding['footer'] }}</p>
</div>
