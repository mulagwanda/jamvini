<aside class="pulse-admin-sidebar">
    <div class="logo">Pulse</div>
    <nav>
        <a href="{{ route('admin.dashboard') }}">{{ jv_icon('layout-dashboard', '', 16) }} Dashboard</a>
        <a href="{{ route('admin.clients.index') }}">{{ jv_icon('users', '', 16) }} Clients</a>
        <a href="{{ route('admin.services.index') }}">{{ jv_icon('package', '', 16) }} Services</a>
        <a href="{{ route('admin.orders.index') }}">{{ jv_icon('shopping-cart', '', 16) }} Orders</a>
        <a href="{{ route('admin.invoices.index') }}">{{ jv_icon('file-text', '', 16) }} Invoices</a>
        <a href="{{ route('admin.settings.index') }}">{{ jv_icon('settings', '', 16) }} Settings</a>
    </nav>
</aside>
