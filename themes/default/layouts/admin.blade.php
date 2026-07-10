<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JamVini Hosting') — Admin Panel</title>
    
    {{-- Theme CSS Assets --}}
    <link rel="stylesheet" href="{{ theme_asset('css/admin.css', 'admin') }}?v={{ filemtime(theme_asset_path('css/admin.css', 'admin')) }}">
    {{-- Theme JS Assets --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body>
<div class="admin-wrapper">
    {{-- SIDEBAR — built from MenuRegistry --}}
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">JH</div>
            <span class="sidebar-brand">JamVini</span>
        </div>
        
        <ul class="sidebar-nav">
    @php
        $sections = \App\Core\Registries\MenuRegistry::getAdminMenuBySection();
        $currentAdmin = auth('admin')->user();
    @endphp
    
    @foreach($sections as $sectionName => $items)
        @if(count($items) > 0)
            <li class="nav-section-label">{{ strtoupper($sectionName) }}</li>
            @foreach($items as $slug => $menu)
                @continue($currentAdmin && !$currentAdmin->canAccess($slug, 'read'))
                <li class="nav-item">
                    @if(!empty($menu['children']))
                        <a href="#submenu-{{ $slug }}" 
                           class="nav-link {{ request()->routeIs($menu['route'] . '*') ? 'active' : '' }}"
                           onclick="toggleSubmenu(event, 'submenu-{{ $slug }}')">
                            {{ jv_icon($menu['icon'] ?? 'file-text', 'icon') }}
                            <span>{{ $menu['label'] }}</span>
                            <span class="nav-chevron">{{ jv_icon('chevron-down', '', 14) }}</span>
                        </a>
                        <ul class="submenu" id="submenu-{{ $slug }}">
                            @foreach($menu['children'] as $child)
                                <li class="nav-item">
                                    <a href="{{ route($child['route']) }}" 
                                       class="nav-link {{ request()->routeIs($child['route'] . '*') ? 'active' : '' }}">
                                        <span>{{ $child['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <a href="{{ route($menu['route'], $menu['route_params'] ?? []) }}" 
                           class="nav-link {{ request()->routeIs($menu['route'] . '*') ? 'active' : '' }}">
                            {{ jv_icon($menu['icon'] ?? 'file-text', 'icon') }}
                            <span>{{ $menu['label'] }}</span>
                        </a>
                    @endif
                </li>
            @endforeach
        @endif
    @endforeach
    
    {{-- System menu (always visible) --}}
    <li class="nav-section-label">SYSTEM</li>
    @if($currentAdmin?->canAccess('admins', 'manage'))
    <li class="nav-item">
        <a href="{{ route('admin.admin-users.index') }}" class="nav-link {{ request()->routeIs('admin.admin-users.*') ? 'active' : '' }}">
            {{ jv_icon('user', 'icon') }}
            <span>Admin Users</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
            {{ jv_icon('building-2', 'icon') }}
            <span>Departments</span>
        </a>
    </li>
    @endif
    @if($currentAdmin?->canAccess('plugins', 'read'))
    <li class="nav-item">
        <a href="{{ route('admin.plugins.index') }}" class="nav-link {{ request()->routeIs('admin.plugins.*') ? 'active' : '' }}">
            {{ jv_icon('puzzle', 'icon') }}
            <span>Plugins</span>
        </a>
    </li>
    @endif
    @if($currentAdmin?->canAccess('system', 'read'))
    <li class="nav-item">
        <a href="{{ route('admin.themes.index') }}" class="nav-link {{ request()->routeIs('admin.themes.*') ? 'active' : '' }}">
            {{ jv_icon('palette', 'icon') }}
            <span>Themes</span>
        </a>
    </li>
    @endif
    @if($currentAdmin?->canAccess('system', 'read'))
    <li class="nav-item">
        <a href="{{ route('admin.system.index') }}" class="nav-link {{ request()->routeIs('admin.system.*') ? 'active' : '' }}">
            {{ jv_icon('monitor', 'icon') }}
            <span>System</span>
        </a>
    </li>
    <li class="nav-item">
    <a href="{{ route('admin.cron.index') }}" class="nav-link {{ request()->routeIs('admin.cron.*') ? 'active' : '' }}">
        {{ jv_icon('clock', 'icon') }}
        <span>Cron Manager</span>
    </a>
</li>
    <li class="nav-item">
        <a href="{{ route('admin.migration.index') }}" class="nav-link {{ request()->routeIs('admin.migration.*') ? 'active' : '' }}">
            {{ jv_icon('database-backup', 'icon') }}
            <span>JV Migration</span>
        </a>
    </li>
    @endif
</ul>
        
        <div class="sidebar-footer">
            <a href="{{ route('admin.logout') }}" 
               class="nav-link"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                {{ jv_icon('log-out', 'icon') }}
                <span>Logout</span>
            </a>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </aside>
    
    {{-- MAIN CONTENT --}}
    <main class="admin-main">
        {{-- TOPBAR --}}
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" title="Toggle sidebar">{{ jv_icon('menu', '', 22) }}</button>
                <nav class="breadcrumb">
                    @yield('breadcrumbs')
                </nav>
            </div>
            
            <div class="topbar-right">
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="topbar-btn" title="View website">
                    {{ jv_icon('external-link', '', 20) }}
                </a>
                <div class="dropdown">
                    <div class="user-dropdown dropdown-trigger">
                        <div class="user-avatar">
                            {{ substr(auth('admin')->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ auth('admin')->user()->name ?? 'Admin' }}</div>
                            <div class="user-role">{{ ucfirst(str_replace('_', ' ', auth('admin')->user()->role ?? 'admin')) }}</div>
                        </div>
                    </div>
                    <div class="dropdown-menu">
                        @if($currentAdmin?->canAccess('settings', 'read'))
                        <a href="{{ route('admin.settings.index') }}" class="dropdown-item">{{ jv_icon('settings', '', 16) }} Settings</a>
                        <div class="dropdown-divider"></div>
                        @endif
                        <a href="{{ route('admin.logout') }}" 
                           class="dropdown-item danger"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ jv_icon('log-out', '', 16) }} Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        {{-- PAGE CONTENT --}}
        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <span class="alert-icon">{{ jv_icon('check-circle', '', 20) }}</span>
                    {{ session('success') }}
                    <button class="alert-close">&times;</button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">
                    <span class="alert-icon">{{ jv_icon('x-circle', '', 20) }}</span>
                    {{ session('error') }}
                    <button class="alert-close">&times;</button>
                </div>
            @endif
            
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger">
                    <span class="alert-icon">{{ jv_icon('triangle-alert', '', 20) }}</span>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button class="alert-close">&times;</button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>
</div>

<script>
window.JamViniConfig = {
    currency: @json(\App\Models\Setting::get('currency', 'TZS')),
    currencyDecimals: {{ (int) \App\Models\Setting::get('currency_decimal_places', \App\Models\Setting::get('currency', 'TZS') === 'TZS' ? '0' : '2') }}
};
</script>
<script src="{{ theme_asset('js/admin.js', 'admin') }}?v={{ filemtime(theme_asset_path('js/admin.js', 'admin')) }}"></script>
@stack('scripts')
</body>
</html>
