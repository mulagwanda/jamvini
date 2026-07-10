<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    public const ACCESS_LEVELS = ['none', 'read', 'write', 'delete', 'manage'];

    public const PERMISSION_MODULES = [
        'dashboard' => 'Dashboard',
        'clients' => 'Clients',
        'orders' => 'Orders',
        'invoices' => 'Invoices',
        'services' => 'Services',
        'domains' => 'Domains',
        'media' => 'Media Library',
        'cms' => 'CMS',
        'social-media-centre' => 'Social Media Centre',
        'support' => 'Support',
        'settings' => 'Settings',
        'plugins' => 'Plugins',
        'system' => 'System Tools',
        'admins' => 'Admin Users',
    ];

    protected $guard = 'admin';

    protected $fillable = [
        'admin_department_id', 'name', 'email', 'password', 'role', 'status',
        'job_title', 'phone', 'avatar', 'permissions', 'last_login_at', 'last_login_ip'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'permissions' => 'array',
        'last_login_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(AdminDepartment::class, 'admin_department_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        [$module, $level] = array_pad(explode('.', $permission, 2), 2, 'read');

        return $this->canAccess($module, $level);
    }

    public function canAccess(string $module, string $requiredLevel = 'read'): bool
    {
        if ($this->isSuperAdmin() || $this->role === 'admin') {
            return true;
        }

        $roleDefaults = $this->roleDefaults();
        $permissions = array_merge($roleDefaults[$this->role] ?? [], $this->permissions ?? []);
        $grantedLevel = $this->highestLevelFor($permissions, $module);

        return $this->levelValue($grantedLevel) >= $this->levelValue($requiredLevel);
    }

    public function permissionLevel(string $module): string
    {
        if ($this->isSuperAdmin() || $this->role === 'admin') {
            return 'manage';
        }

        $roleDefaults = $this->roleDefaults();
        $permissions = array_merge($roleDefaults[$this->role] ?? [], $this->permissions ?? []);

        return $this->highestLevelFor($permissions, $module);
    }

    protected function highestLevelFor(array $permissions, string $module): string
    {
        $highest = 'none';

        foreach ($permissions as $permission) {
            [$permissionModule, $permissionLevel] = array_pad(explode('.', (string) $permission, 2), 2, 'read');

            if ($permissionModule === $module && $this->levelValue($permissionLevel) > $this->levelValue($highest)) {
                $highest = $permissionLevel;
            }
        }

        return $highest;
    }

    protected function levelValue(string $level): int
    {
        return array_search($level, self::ACCESS_LEVELS, true) ?: 0;
    }

    protected function roleDefaults(): array
    {
        return [
            'manager' => [
                'dashboard.read', 'clients.manage', 'orders.manage', 'invoices.manage',
                'services.write', 'domains.write', 'media.manage', 'cms.write',
                'social-media-centre.manage', 'support.manage', 'settings.read',
            ],
            'billing' => [
                'dashboard.read', 'clients.read', 'orders.read', 'invoices.manage',
            ],
            'support' => [
                'dashboard.read', 'clients.read', 'orders.read', 'services.read', 'domains.read', 'media.read', 'support.manage', 'ai-assistant.manage',
            ],
            'technical' => [
                'dashboard.read', 'clients.read', 'orders.write', 'services.manage', 'domains.manage',
            ],
            'viewer' => [
                'dashboard.read', 'clients.read', 'orders.read', 'invoices.read',
                'services.read', 'domains.read', 'media.read', 'cms.read',
                'social-media-centre.read', 'support.read',
            ],
        ];
    }
}
