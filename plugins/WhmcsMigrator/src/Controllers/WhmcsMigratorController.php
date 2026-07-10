<?php

namespace Plugins\WhmcsMigrator\src\Controllers;

use App\Core\Hooks\Action;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Plugins\WhmcsMigrator\src\Models\WhmcsMigrationBatch;

class WhmcsMigratorController extends Controller
{
    public function index()
    {
        $batches = WhmcsMigrationBatch::latest()->paginate(15);

        return view('plugins.WhmcsMigrator::admin.index', compact('batches'));
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'source_type' => 'required|in:archive,csv,json',
            'file' => 'required|file|max:51200',
            'notes' => 'nullable|string|max:2000',
        ]);

        $path = $request->file('file')->store('whmcs-migrations');

        $batch = WhmcsMigrationBatch::create([
            'name' => $validated['name'],
            'source_type' => $validated['source_type'],
            'file_path' => $path,
            'notes' => $validated['notes'] ?? null,
            'status' => 'uploaded',
        ]);

        Action::do('migration.batch_created', $batch);

        return back()->with('success', 'WHMCS migration batch uploaded.');
    }

    public function analyze(WhmcsMigrationBatch $batch)
    {
        $size = $batch->file_path && Storage::exists($batch->file_path) ? Storage::size($batch->file_path) : 0;
        $batch->update([
            'status' => 'analyzed',
            'summary' => [
                'file_size' => $size,
                'detected' => ['clients', 'services', 'domains', 'invoices', 'tickets'],
                'next_step' => 'Map WHMCS fields to JamVini entities before import.',
            ],
        ]);

        Action::do('migration.batch_analyzed', $batch);

        return back()->with('success', 'Migration batch analyzed.');
    }
}
