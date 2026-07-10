<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Core\Registries\PermissionRegistry;
use App\Models\Admin;
use App\Models\AdminDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    protected array $roles = [
        'super_admin' => 'Super Admin',
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'billing' => 'Billing Staff',
        'support' => 'Support Staff',
        'technical' => 'Technical Staff',
        'viewer' => 'Read Only',
    ];

    protected array $permissions = [
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
        'admins' => 'Admin Users & Departments',
    ];

    protected array $accessLevels = [
        'none' => 'No Access',
        'read' => 'Read Only',
        'write' => 'Read & Write',
        'delete' => 'Delete',
        'manage' => 'Manage',
    ];

    public function index(Request $request)
    {
        $this->ensureDefaultDepartments();

        $permissions = $this->permissionModules();

        $admins = Admin::with('department')
            ->when($request->search, fn ($query, $search) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%");
            }))
            ->when($request->role, fn ($query, $role) => $query->where('role', $role))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Admin::count(),
            'active' => Admin::where('status', 'active')->count(),
            'inactive' => Admin::where('status', 'inactive')->count(),
            'departments' => AdminDepartment::where('is_active', true)->count(),
        ];

        return view('admin.admin-users.index', [
            'admins' => $admins,
            'stats' => $stats,
            'roles' => $this->roles,
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        $this->ensureDefaultDepartments();

        return view('admin.admin-users.form', [
            'adminUser' => new Admin(['status' => 'active', 'role' => 'support']),
            'departments' => AdminDepartment::where('is_active', true)->orderBy('name')->get(),
            'roles' => $this->roles,
            'permissions' => $this->permissionModules(),
            'accessLevels' => $this->accessLevels,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['permissions'] = $this->normalizePermissions($request->input('access', []));

        Admin::create($validated);

        return redirect()->route('admin.admin-users.index')->with('success', 'Admin user created.');
    }

    public function edit(Admin $adminUser)
    {
        $this->ensureDefaultDepartments();

        return view('admin.admin-users.form', [
            'adminUser' => $adminUser,
            'departments' => AdminDepartment::where('is_active', true)->orderBy('name')->get(),
            'roles' => $this->roles,
            'permissions' => $this->permissionModules(),
            'accessLevels' => $this->accessLevels,
        ]);
    }

    public function update(Request $request, Admin $adminUser)
    {
        $validated = $request->validate($this->rules($adminUser));

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $validated['permissions'] = $this->normalizePermissions($request->input('access', []));
        $adminUser->update($validated);

        return redirect()->route('admin.admin-users.index')->with('success', 'Admin user updated.');
    }

    public function destroy(Admin $adminUser)
    {
        if ($adminUser->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own admin account.');
        }

        $adminUser->delete();

        return redirect()->route('admin.admin-users.index')->with('success', 'Admin user deleted.');
    }

    protected function rules(?Admin $admin = null): array
    {
        return [
            'admin_department_id' => 'nullable|exists:admin_departments,id',
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('admins', 'email')->ignore($admin?->id)],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::in(array_keys($this->roles))],
            'status' => 'required|in:active,inactive,suspended',
            'job_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'access' => 'nullable|array',
            'access.*' => ['nullable', Rule::in(array_keys($this->accessLevels))],
        ];
    }

    protected function normalizePermissions(array $access): array
    {
        $permissions = [];

        foreach ($this->permissionModules() as $module => $label) {
            $level = $access[$module] ?? 'none';

            if ($level !== 'none') {
                $permissions[] = $module . '.' . $level;
            }
        }

        return $permissions;
    }

    protected function permissionModules(): array
    {
        return array_merge($this->permissions, PermissionRegistry::modules());
    }

    protected function ensureDefaultDepartments(): void
    {
        foreach ([
            'Management' => 'Owners, directors, and senior operations leads',
            'Billing' => 'Invoices, payments, collections, and client accounts',
            'Support' => 'Customer care, tickets, and onboarding',
            'Technical' => 'Hosting provisioning, domains, DNS, and servers',
            'Sales' => 'Leads, quotes, orders, and renewals',
        ] as $name => $description) {
            AdminDepartment::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description, 'is_active' => true]
            );
        }
    }
}
