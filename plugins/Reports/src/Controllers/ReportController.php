<?php

namespace Plugins\Reports\src\Controllers;

use App\Core\Hooks\Action;
use App\Core\Registries\ReportRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        $groups = ReportRegistry::grouped();

        return view('plugins.Reports::admin.index', compact('groups'));
    }

    public function show(Request $request, string $key)
    {
        $report = ReportRegistry::get($key);
        abort_unless($report, 404);

        $filters = $this->filters($request);
        $result = ReportRegistry::run($key, $filters);

        return view('plugins.Reports::admin.show', compact('report', 'result', 'filters'));
    }

    public function exportCsv(Request $request, string $key): StreamedResponse
    {
        $report = ReportRegistry::get($key);
        abort_unless($report, 404);

        $filters = $this->filters($request);
        $result = ReportRegistry::run($key, $filters);
        $filename = str($key)->replace('.', '-')->slug() . '-' . now()->format('Ymd-His') . '.csv';

        Action::do('reports.exported', $report, $filters);

        return response()->streamDownload(function () use ($result) {
            $out = fopen('php://output', 'w');
            $columns = $result['columns'] ?? [];
            fputcsv($out, array_map(fn ($column) => $column['label'], $columns));

            foreach (($result['rows'] ?? []) as $row) {
                fputcsv($out, array_map(fn ($column) => $row[$column['key']] ?? '', $columns));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function filters(Request $request): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'string', 'max:80'],
        ]);
    }
}
