<?php

namespace App\Http\Controllers\Admin;

use App\Core\Packages\JamviniPackageExporter;
use App\Core\Packages\JamviniPackageImporter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JamviniMigrationController extends Controller
{
    public function index()
    {
        $defaultSections = ['settings', 'cms', 'menus', 'clients', 'plugins'];

        return view('admin.migration.index', compact('defaultSections'));
    }

    public function export(Request $request, JamviniPackageExporter $exporter)
    {
        $validated = $request->validate([
            'sections' => ['nullable', 'array'],
            'sections.*' => ['string', 'in:settings,cms,menus,clients,plugins'],
        ]);

        $sections = $validated['sections'] ?? ['settings', 'cms', 'menus', 'clients', 'plugins'];
        $package = $exporter->migration($sections);
        $filename = 'jamvini-migration-' . now()->format('Ymd-His') . '.json';

        return response()->streamDownload(function () use ($package) {
            echo json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function import(Request $request, JamviniPackageImporter $importer)
    {
        $validated = $request->validate([
            'package' => ['required', 'file', 'mimes:json,txt', 'max:51200'],
        ]);

        $package = json_decode(file_get_contents($validated['package']->getRealPath()), true);
        if (!is_array($package)) {
            return back()->with('error', 'The uploaded JamVini package is not valid JSON.');
        }

        $result = $importer->importArray($package);
        $counts = collect($result['counts'] ?? [])
            ->map(fn ($count, $name) => str($name)->headline() . ': ' . $count)
            ->implode(', ');

        return back()->with($result['success'] ? 'success' : 'error', trim(($result['message'] ?? 'Import complete.') . ' ' . $counts));
    }
}
