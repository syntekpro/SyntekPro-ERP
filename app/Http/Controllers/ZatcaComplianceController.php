<?php

namespace App\Http\Controllers;

use App\Models\ZatcaCertificate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class ZatcaComplianceController extends Controller
{
    public function index(): View
    {
        $this->authorizeAccess();

        return view('zatca.index');
    }

    public function downloadCsr(ZatcaCertificate $zatcaCertificate): Response
    {
        $this->authorizeAccess();

        abort_if($zatcaCertificate->csr === null, 404);

        return response($zatcaCertificate->csr, 200, [
            'Content-Type' => 'application/x-pem-file',
            'Content-Disposition' => 'attachment; filename="zatca-csr-'.$zatcaCertificate->id.'.pem"',
        ]);
    }

    public function downloadPrivateKey(ZatcaCertificate $zatcaCertificate): Response
    {
        $this->authorizeAccess();

        abort_if($zatcaCertificate->private_key_encrypted === null, 404);

        $privateKey = Crypt::decryptString($zatcaCertificate->private_key_encrypted);

        return response($privateKey, 200, [
            'Content-Type' => 'application/x-pem-file',
            'Content-Disposition' => 'attachment; filename="zatca-private-key-'.$zatcaCertificate->id.'.pem"',
        ]);
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAccountant() || $user->hasPermission('settings.manage')), 403);
    }
}
