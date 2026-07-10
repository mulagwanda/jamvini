<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Core\PluginManager;
use App\Core\UpdateManager;
use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginController extends Controller
{
    protected PluginManager $manager;
    protected UpdateManager $updater;

    public function __construct(PluginManager $manager, UpdateManager $updater)
    {
        $this->manager = $manager;
        $this->updater = $updater;
    }

    public function index()
    {
        $availablePlugins = $this->manager->scan();
        $installedPlugins = Plugin::all();

        return view('admin.plugins.index', compact('availablePlugins', 'installedPlugins'));
    }

    public function install(Request $request)
    {
        $slug = $request->input('slug');

        if ($this->manager->install($slug)) {
            $this->manager->activate($slug);
            return $this->respond($request, true, 'Plugin installed and activated!');
        }

        return $this->respond($request, false, 'Failed to install plugin.');
    }

    public function activate(Request $request)
    {
        $slug = $request->input('slug');

        if ($this->manager->activate($slug)) {
            return $this->respond($request, true, 'Plugin activated!');
        }

        return $this->respond($request, false, 'Failed to activate plugin.');
    }

    public function deactivate(Request $request)
    {
        $slug = $request->input('slug');

        if ($this->manager->deactivate($slug)) {
            return $this->respond($request, true, 'Plugin deactivated!');
        }

        return $this->respond($request, false, 'Cannot deactivate system plugin.');
    }

    public function uninstall(Request $request)
    {
        $slug = $request->input('slug');

        if ($this->manager->uninstall($slug)) {
            return $this->respond($request, true, 'Plugin uninstalled!');
        }

        return $this->respond($request, false, 'Cannot uninstall system plugin.');
    }

    public function update(Request $request, string $slug)
    {
        $result = $this->updater->installPluginUpdate($slug);

        if ($result['success']) {
            return $this->respond($request, true, "Plugin updated to v{$result['version']}!");
        }

        return $this->respond($request, false, $result['message'] ?? 'Update failed.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'plugin_zip' => 'required|file|mimes:zip|max:10240',
        ]);

        $file = $request->file('plugin_zip');
        $zipPath = $file->storeAs('temp', 'uploaded_plugin.zip');

        // Extract and install
        $extractPath = storage_path('app/temp/uploaded_plugin');
        
        $zip = new \ZipArchive();
        if ($zip->open(storage_path('app/' . $zipPath)) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        }

        // Find plugin.json in extracted files
        $manifestFile = $this->findManifest($extractPath);
        
        if (!$manifestFile) {
            return $this->respond($request, false, 'Invalid plugin ZIP. No plugin.json found.');
        }

        $manifest = json_decode(file_get_contents($manifestFile), true);
        $slug = PluginManager::normalizeSlug($manifest['slug'] ?? basename(dirname($manifestFile)));

        // Move to plugins directory
        $pluginPath = base_path("plugins/{$slug}");
        if (File::exists($pluginPath)) {
            $this->deleteDir($extractPath);
            @unlink(storage_path('app/' . $zipPath));

            return $this->respond($request, false, "Plugin '{$slug}' already exists.");
        }

        File::moveDirectory(dirname($manifestFile), $pluginPath);

        // Install
        $this->manager->install($slug);
        $this->manager->activate($slug);

        // Cleanup
        @unlink(storage_path('app/' . $zipPath));
        $this->deleteDir($extractPath);

        return $this->respond($request, true, "Plugin '{$manifest['name']}' uploaded and installed!");
    }

    public function editor(Request $request, string $slug)
    {
        $slug = PluginManager::normalizeSlug($slug);
        $pluginPath = $this->manager->pluginPath($slug);
        abort_unless(File::exists($pluginPath), 404);

        $manifest = $this->manager->getManifest($slug) ?: [];
        $files = $this->editableFiles($pluginPath);
        $selected = $request->query('file', $files[0]['path'] ?? 'plugin.json');
        $selected = $this->safeRelativePath($selected);
        abort_unless($this->isEditableFile($pluginPath, $selected), 404);

        $content = File::get($pluginPath . '/' . $selected);

        return view('admin.plugins.editor', compact('slug', 'manifest', 'files', 'selected', 'content'));
    }

    public function updateFile(Request $request, string $slug)
    {
        $slug = PluginManager::normalizeSlug($slug);
        $pluginPath = $this->manager->pluginPath($slug);
        abort_unless(File::exists($pluginPath), 404);

        $validated = $request->validate([
            'file' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:600000'],
        ]);

        $file = $this->safeRelativePath($validated['file']);
        abort_unless($this->isEditableFile($pluginPath, $file), 422);

        File::put($pluginPath . '/' . $file, $validated['content'] ?? '');

        if (str_ends_with($file, '.php')) {
            $check = trim((string) shell_exec('php -l ' . escapeshellarg($pluginPath . '/' . $file) . ' 2>&1'));
            if (!str_contains($check, 'No syntax errors detected')) {
                return redirect()->route('admin.plugins.editor', ['slug' => $slug, 'file' => $file])
                    ->with('error', 'File saved, but PHP syntax check reported: ' . $check);
            }
        }

        return redirect()->route('admin.plugins.editor', ['slug' => $slug, 'file' => $file])
            ->with('success', 'Plugin file saved.');
    }

    protected function respond(Request $request, bool $success, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'redirect' => route('admin.plugins.index'),
            ], $success ? 200 : 422);
        }

        return redirect()->route('admin.plugins.index')
            ->with($success ? 'success' : 'error', $message);
    }

    protected function findManifest(string $dir): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getFilename() === 'plugin.json') {
                return $file->getPathname();
            }
        }

        return null;
    }

    protected function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file) : unlink($file);
        }
        rmdir($dir);
    }

    protected function editableFiles(string $pluginPath): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($pluginPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relative = str_replace($pluginPath . '/', '', $file->getPathname());
            if ($this->isEditableFile($pluginPath, $relative)) {
                $files[] = [
                    'path' => $relative,
                    'name' => basename($relative),
                    'size' => $file->getSize(),
                ];
            }
        }

        usort($files, fn ($a, $b) => strcmp($a['path'], $b['path']));

        return $files;
    }

    protected function isEditableFile(string $pluginPath, string $relative): bool
    {
        $relative = $this->safeRelativePath($relative);
        $fullPath = $pluginPath . '/' . $relative;
        $realPlugin = realpath($pluginPath);
        $realFile = realpath($fullPath);

        if (!$realPlugin || !$realFile || !str_starts_with($realFile, $realPlugin . DIRECTORY_SEPARATOR)) {
            return false;
        }

        if (Str::contains($relative, ['vendor/', 'node_modules/', 'storage/', '.git/', '.env'])) {
            return false;
        }

        if (filesize($realFile) > 600000) {
            return false;
        }

        return $this->isAllowedEditorExtension($relative);
    }

    protected function isAllowedEditorExtension(string $path): bool
    {
        return str_ends_with($path, '.blade.php')
            || in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), [
                'php', 'json', 'js', 'css', 'md', 'txt', 'xml', 'yml', 'yaml', 'stub',
            ], true);
    }

    protected function safeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        $path = ltrim((string) $path, '/');
        $parts = [];

        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($parts);
                continue;
            }
            $parts[] = $part;
        }

        return implode('/', $parts);
    }
}
