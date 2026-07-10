<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    protected $steps = ['requirements', 'database', 'admin', 'complete'];

    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/install/installed');
        }

        return redirect('/install/requirements');
    }

    public function step($step, Request $request)
    {
        if ($this->isInstalled() && $step !== 'complete') {
            return redirect('/install/installed');
        }

        if (!in_array($step, $this->steps, true)) {
            return redirect('/install/requirements');
        }

        $data = ['step' => $step];
        $method = 'step' . ucfirst($step);

        if (method_exists($this, $method)) {
            return $this->$method($request);
        }

        return view('installer.' . $step, $data);
    }

    public function installed()
    {
        if (!$this->isInstalled()) {
            return redirect('/install');
        }

        return view('installer.installed', ['step' => 'complete']);
    }

    protected function stepRequirements()
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP >= 8.2',
                'passed' => version_compare(PHP_VERSION, '8.2', '>='),
                'current' => PHP_VERSION,
            ],
            'bcmath' => ['name' => 'BCMath Extension', 'passed' => extension_loaded('bcmath')],
            'ctype' => ['name' => 'Ctype Extension', 'passed' => extension_loaded('ctype')],
            'curl' => ['name' => 'cURL Extension', 'passed' => extension_loaded('curl')],
            'dom' => ['name' => 'DOM Extension', 'passed' => extension_loaded('dom')],
            'fileinfo' => ['name' => 'Fileinfo Extension', 'passed' => extension_loaded('fileinfo')],
            'json' => ['name' => 'JSON Extension', 'passed' => extension_loaded('json')],
            'mbstring' => ['name' => 'Mbstring Extension', 'passed' => extension_loaded('mbstring')],
            'openssl' => ['name' => 'OpenSSL Extension', 'passed' => extension_loaded('openssl')],
            'pdo' => ['name' => 'PDO Extension', 'passed' => extension_loaded('pdo')],
            'pdo_mysql' => ['name' => 'PDO MySQL', 'passed' => extension_loaded('pdo_mysql')],
            'tokenizer' => ['name' => 'Tokenizer Extension', 'passed' => extension_loaded('tokenizer')],
            'xml' => ['name' => 'XML Extension', 'passed' => extension_loaded('xml')],
            'env_writable' => [
                'name' => '.env file writable',
                'passed' => is_writable(base_path('.env')) || is_writable(base_path()),
            ],
            'storage_writable' => [
                'name' => 'Storage writable',
                'passed' => is_writable(storage_path()),
            ],
        ];

        $allPassed = collect($requirements)->every(fn($r) => $r['passed']);

        return view('installer.requirements', compact('requirements', 'allPassed'));
    }

    protected function stepDatabase(Request $request)
    {
        $this->ensureEnvironmentFile();

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'db_host' => 'required|string',
                'db_port' => 'required|string',
                'db_database' => 'required|string',
                'db_username' => 'required|string',
                'db_password' => 'nullable|string',
            ]);

            try {
                $this->updateEnv([
                    'DB_URL' => '',
                    'DB_CONNECTION' => 'mysql',
                    'DB_HOST' => $validated['db_host'],
                    'DB_PORT' => $validated['db_port'],
                    'DB_DATABASE' => $validated['db_database'],
                    'DB_USERNAME' => $validated['db_username'],
                    'DB_PASSWORD' => $validated['db_password'],
                ]);

                Artisan::call('config:clear');
                $this->configureRuntimeDatabase($validated);
                
                // Test connection
                DB::connection('mysql')->getPdo();

                return redirect('/install/admin')
                    ->with('db_ok', true);
            } catch (\Exception $e) {
                return back()
                    ->withInput($request->except('db_password'))
                    ->with('error', $this->friendlyDatabaseError($e));
            }
        }

        return view('installer.database');
    }

    protected function stepAdmin(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
                'company_name' => 'nullable|string|max:255',
            ]);

            try {
                // Run migrations
                $this->ensureEnvironmentFile();
                $this->ensureApplicationKey();
                Artisan::call('migrate', ['--force' => true]);
                $this->installCorePlugins();

                // Create admin user for the admin guard.
                \App\Models\Admin::updateOrCreate([
                    'email' => $validated['email'],
                ], [
                    'name' => $validated['name'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'admin',
                ]);

                // Save company name
                \App\Models\Setting::set('company_name', $validated['company_name'] ?? 'My Company');

                // Mark as installed
                $this->markInstalled();

                return redirect('/install/complete');
            } catch (\Exception $e) {
                report($e);

                return back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->with('error', 'Installation could not be completed. Please confirm the database user has permission to create tables, then try again.');
            }
        }

        return view('installer.admin');
    }

    protected function stepComplete()
    {
        return view('installer.complete');
    }

    protected function updateEnv(array $data): void
    {
        $this->ensureEnvironmentFile();

        $envFile = base_path('.env');
        $content = File::get($envFile);

        foreach ($data as $key => $value) {
            $line = $key . '=' . $this->quoteEnvValue($value);

            if (str_contains($content, $key . '=')) {
                $content = preg_replace_callback(
                    "/^{$key}=.*/m",
                    fn () => $line,
                    $content
                );
            } else {
                $content .= "\n{$line}";
            }
        }

        File::put($envFile, $content);
    }

    protected function configureRuntimeDatabase(array $settings): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.url' => null,
            'database.connections.mysql.host' => $settings['db_host'],
            'database.connections.mysql.port' => $settings['db_port'],
            'database.connections.mysql.database' => $settings['db_database'],
            'database.connections.mysql.username' => $settings['db_username'],
            'database.connections.mysql.password' => $settings['db_password'] ?? '',
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
        DB::setDefaultConnection('mysql');
    }

    protected function quoteEnvValue(?string $value): string
    {
        $value = str_replace(["\\", '"', "\r", "\n"], ["\\\\", '\"', '', ''], (string) $value);

        return '"' . $value . '"';
    }

    protected function ensureEnvironmentFile(): void
    {
        $envFile = base_path('.env');
        $exampleFile = base_path('.env.example');

        if (!File::exists($envFile) && File::exists($exampleFile)) {
            File::copy($exampleFile, $envFile);
        }
    }

    protected function ensureApplicationKey(): void
    {
        $envFile = base_path('.env');

        if (!File::exists($envFile)) {
            return;
        }

        $content = File::get($envFile);
        if (preg_match('/^APP_KEY=(.+)$/m', $content, $matches) && trim($matches[1], "\"' ") !== '') {
            return;
        }

        Artisan::call('key:generate', ['--force' => true]);
        Artisan::call('config:clear');
    }

    protected function installCorePlugins(): void
    {
        $manager = app(\App\Core\PluginManager::class);
        $manager->initialize();
        $plugins = $this->sortPluginsByDependencies($manager->scan());

        foreach ($plugins as $slug => $plugin) {
            if ($plugin['core'] ?? false) {
                $manager->runMigrations($slug);
            }
        }
    }

    protected function sortPluginsByDependencies(array $plugins): array
    {
        $sorted = [];
        $visiting = [];

        $visit = function (string $slug) use (&$visit, &$plugins, &$sorted, &$visiting): void {
            if (isset($sorted[$slug]) || !isset($plugins[$slug])) {
                return;
            }

            if (isset($visiting[$slug])) {
                return;
            }

            $visiting[$slug] = true;

            foreach ($plugins[$slug]['dependencies'] ?? [] as $dependency) {
                $visit(\App\Core\PluginManager::normalizeSlug($dependency));
            }

            unset($visiting[$slug]);
            $sorted[$slug] = $plugins[$slug];
        };

        foreach (array_keys($plugins) as $slug) {
            $visit($slug);
        }

        return $sorted;
    }

    protected function isInstalled(): bool
    {
        return File::exists(storage_path('app/installed'));
    }

    protected function markInstalled(): void
    {
        File::put(storage_path('app/installed'), date('Y-m-d H:i:s'));
    }

    protected function friendlyDatabaseError(\Throwable $e): string
    {
        $message = $e->getMessage();

        if (Str::contains($message, ['Access denied', '1045'])) {
            return 'Database login failed. Please check the database username and password.';
        }

        if (Str::contains($message, ['Unknown database', '1049'])) {
            return 'The database name was not found. Please create the database first or check the name.';
        }

        if (Str::contains($message, ['Connection refused', '2002', 'getaddrinfo', 'php_network_getaddresses'])) {
            return 'Could not connect to the database server. Please check the host and port.';
        }

        return 'Database connection failed. Please check the database details and try again.';
    }
}
