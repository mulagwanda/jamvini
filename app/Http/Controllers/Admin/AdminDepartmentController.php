<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminDepartmentController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureDefaults();

        $departments = AdminDepartment::withCount('admins')
            ->when($request->search, fn ($query, $search) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
            ->orderBy('name')
            ->paginate(15);

        $stats = [
            'total' => AdminDepartment::count(),
            'active' => AdminDepartment::where('is_active', true)->count(),
            'inactive' => AdminDepartment::where('is_active', false)->count(),
            'assigned_admins' => AdminDepartment::withCount('admins')->get()->sum('admins_count'),
        ];

        return view('admin.departments.index', compact('departments', 'stats'));
    }

    public function create()
    {
        return view('admin.departments.form', [
            'department' => new AdminDepartment(['is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        AdminDepartment::create($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Department created.');
    }

    public function edit(AdminDepartment $department)
    {
        return view('admin.departments.form', compact('department'));
    }

    public function update(Request $request, AdminDepartment $department)
    {
        $validated = $this->validated($request, $department);
        $department->update($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Department updated.');
    }

    public function destroy(AdminDepartment $department)
    {
        if ($department->admins()->exists()) {
            return back()->with('error', 'Move admins out of this department before deleting it.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', 'Department deleted.');
    }

    protected function validated(Request $request, ?AdminDepartment $department = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('admin_departments', 'slug')->ignore($department?->id)],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['slug'] ?: $validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    protected function ensureDefaults(): void
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
