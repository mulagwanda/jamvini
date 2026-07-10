<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        File::delete(storage_path('app/installed'));
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('app/installed'));

        parent::tearDown();
    }

    public function test_homepage_redirects_to_installer_before_installation(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/install');
    }

    public function test_invalid_installer_step_redirects_to_requirements(): void
    {
        $response = $this->get('/install/not-a-step');

        $response->assertRedirect('/install/requirements');
    }

    public function test_installer_is_locked_after_installation(): void
    {
        File::put(storage_path('app/installed'), now()->toDateTimeString());

        $this->get('/install')->assertRedirect('/install/installed');
        $this->get('/install/database')->assertRedirect('/install/installed');
        $this->get('/install/installed')->assertOk();
    }

    public function test_admin_step_completes_installation(): void
    {
        $response = $this->post('/install/admin', [
            'name' => 'JamVini Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'company_name' => 'JamVini Hosting',
        ]);

        $response->assertRedirect('/install/complete');

        $this->assertTrue(File::exists(storage_path('app/installed')));
        $this->assertTrue(Admin::where('email', 'admin@example.test')->exists());
        $this->assertTrue(Schema::hasTable('clients'));
        $this->assertTrue(Schema::hasTable('services'));
        $this->assertTrue(Schema::hasTable('invoices'));
        $this->assertTrue(Schema::hasTable('menus'));
        $this->assertTrue(Schema::hasTable('menu_items'));
    }
}
