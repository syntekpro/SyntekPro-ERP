<?php

namespace App\Http\Controllers;

use App\Models\DocumentShare;
use App\Mail\DocumentLinkMail;
use App\Services\Documents\PrintableDocumentService;
use App\Services\Settings\BusinessSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentOutputController extends Controller
{
    public function print(string $type, int $id, Request $request, PrintableDocumentService $documents, BusinessSettingsService $settings): View
    {
        $document = $documents->find($type, $id);
        $format = $request->query('format') === 'receipt' && $type === 'sale' ? 'receipt' : 'standard';

        return view('documents.print', [
            'document' => $documents->data($type, $document),
            'businessSettings' => $settings->current(),
            'logoUrl' => $settings->logoUrl(),
            'format' => $format,
        ]);
    }

    public function share(string $type, int $id, PrintableDocumentService $documents): RedirectResponse
    {
        $documents->find($type, $id);

        $share = DocumentShare::query()->create([
            'document_type' => $type,
            'document_id' => $id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays((int) config('documents.share_expiry_days', 30)),
            'created_by' => Auth::id(),
        ]);

        return back()->with('status', 'Share link created: '.route('documents.shared', $share->token));
    }

    public function email(string $type, int $id, Request $request, PrintableDocumentService $documents, BusinessSettingsService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $document = $documents->find($type, $id);
        $data = $documents->data($type, $document);
        $businessSettings = $settings->current();

        $share = DocumentShare::query()->create([
            'document_type' => $type,
            'document_id' => $id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays((int) config('documents.share_expiry_days', 30)),
            'created_by' => Auth::id(),
        ]);

        Mail::to($validated['email'])->send(new DocumentLinkMail(
            $data['type'],
            $data['document_number'],
            route('documents.shared', $share->token),
            $businessSettings->mail_from_address ?: config('mail.from.address'),
            $businessSettings->mail_from_name ?: config('mail.from.name'),
        ));

        return back()->with('status', 'Document email queued for '.$validated['email'].'.');
    }

    public function shared(string $token, PrintableDocumentService $documents, BusinessSettingsService $settings): View
    {
        $share = DocumentShare::query()->where('token', $token)->firstOrFail();
        abort_unless($share->isViewable(), 403);

        $document = $documents->find($share->document_type, (int) $share->document_id);

        return view('documents.print', [
            'document' => $documents->data($share->document_type, $document),
            'businessSettings' => $settings->current(),
            'logoUrl' => $settings->logoUrl(),
            'format' => 'standard',
        ]);
    }

    public function shares(): View
    {
        return view('documents.shares', [
            'shares' => DocumentShare::query()->latest()->paginate(20),
        ]);
    }

    public function revoke(DocumentShare $share): RedirectResponse
    {
        $share->update(['revoked_at' => now()]);

        return back()->with('status', 'Share link revoked.');
    }
}
