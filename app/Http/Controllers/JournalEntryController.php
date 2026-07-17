<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', JournalEntry::class);

        return view('journal-entries.index');
    }

    public function create(): View
    {
        $this->authorize('create', JournalEntry::class);

        return view('journal-entries.create');
    }
}
