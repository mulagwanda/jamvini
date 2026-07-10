@extends('themes.default::layouts.admin')

@section('title', 'Edit Invoice #' . $invoice->invoice_number)
@section('breadcrumbs')<a href="{{ route('admin.invoices.index') }}">Invoices</a> <span class="separator">/</span> <a href="{{ route('admin.invoices.show', $invoice) }}">#{{ $invoice->invoice_number }}</a> <span class="separator">/</span> <span class="current">Edit</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Invoice #{{ $invoice->invoice_number }}</h1>
        <p class="page-subtitle">{{ $invoice->client->full_name }}</p>
    </div>
    <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" data-confirm="Delete this invoice?" data-danger="true">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger">🗑️ Delete</button>
    </form>
</div>

<form action="{{ route('admin.invoices.update', $invoice) }}" method="POST" id="invoiceForm">
    @csrf @method('PUT')
    
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>📄 Invoice Details</h3></div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Client</label>
                <select name="client_id" class="form-select" required>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id', $invoice->client_id) == $c->id ? 'selected' : '' }}>{{ $c->full_name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Invoice Number</label>
                <input type="text" class="form-input" value="{{ $invoice->invoice_number }}" readonly>
                <input type="hidden" name="invoice_number" value="{{ $invoice->invoice_number }}">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
            <div class="form-group">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-input" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['draft', 'sent', 'partial', 'overdue', 'cancelled'] as $status)
                        <option value="{{ $status }}" {{ old('status', $invoice->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 120px 1fr 1fr 1fr; gap: 16px; margin-top: 16px;">
            <div class="form-group"><label class="form-label">Currency</label><input type="text" name="currency" class="form-input" value="{{ old('currency', $invoice->currency ?? \App\Models\Setting::get('currency', 'TZS')) }}" maxlength="3"></div>
            <div class="form-group"><label class="form-label">Discount</label><input type="number" name="discount" class="form-input" value="{{ old('discount', $invoice->discount ?? 0) }}" step="0.01" min="0"></div>
            <div class="form-group"><label class="form-label">Source</label><input type="text" name="source" class="form-input" value="{{ old('source', $invoice->source ?? 'admin') }}"></div>
            <div class="form-group"><label class="form-label">External / WHMCS ID</label><input type="text" name="external_id" class="form-input" value="{{ old('external_id', $invoice->external_id) }}"></div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head" style="display: flex; justify-content: space-between;">
            <h3>📋 Line Items</h3>
            <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">➕ Add Item</button>
        </div>
        <div class="table-container">
            <table class="table" id="itemsTable" style="margin: 0;">
                <thead><tr><th>Description</th><th style="width:100px;">Qty</th><th style="width:140px;">Unit Price ({{ \App\Models\Setting::get('currency', 'TZS') }})</th><th style="width:100px;">{{ jv_tax_label() }} %</th><th style="width:140px;">Total</th><th style="width:40px;"></th></tr></thead>
                <tbody id="itemsBody">
                    @foreach(old('items', $invoice->items->toArray()) as $i => $item)
                    <tr class="item-row">
                        <td><input type="text" name="items[{{ $i }}][description]" class="form-input" value="{{ $item['description'] }}" required></td>
                        <td><input type="number" name="items[{{ $i }}][quantity]" class="form-input qty" value="{{ $item['quantity'] }}" min="1" required onchange="calcRow(this)"></td>
                        <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-input price" value="{{ $item['unit_price'] }}" step="0.01" min="0" required onchange="calcRow(this)"></td>
                        <td><input type="number" name="items[{{ $i }}][tax_rate]" class="form-input tax" value="{{ $item['tax_rate'] ?? 0 }}" step="0.01" min="0" max="100" onchange="calcRow(this)"></td>
                        <td><input type="text" class="form-input item-total" value="{{ jv_format_money($item['total'] ?? 0) }}" readonly></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">✕</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
            <div style="width: 280px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0;"><span>Subtotal:</span><strong id="subtotalDisplay">{{ jv_format_money($invoice->subtotal) }}</strong></div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;"><span>{{ jv_tax_label() }}:</span><strong id="taxDisplay">{{ jv_format_money($invoice->tax_amount) }}</strong></div>
                <div style="display: flex; justify-content: space-between; padding: 12px 0; border-top: 2px solid var(--jv-gray-200); font-size: 1.1rem;"><span>Total:</span><strong id="totalDisplay">{{ jv_format_money($invoice->total) }}</strong></div>
            </div>
        </div>
    </div>

    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>📝 Notes</h3></div>
        <textarea name="notes" class="form-textarea" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px;">
            <textarea name="payment_terms" class="form-textarea" rows="3" placeholder="Payment terms shown to client...">{{ old('payment_terms', $invoice->payment_terms) }}</textarea>
            <textarea name="admin_notes" class="form-textarea" rows="3" placeholder="Private billing notes...">{{ old('admin_notes', $invoice->admin_notes) }}</textarea>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">💾 Update Invoice</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
let itemIndex = {{ count(old('items', $invoice->items->toArray())) }};
const moneyCurrency = @json(\App\Models\Setting::get('currency', 'TZS'));
const moneyDecimals = {{ (int) \App\Models\Setting::get('currency_decimal_places', \App\Models\Setting::get('currency', 'TZS') === 'TZS' ? '0' : '2') }};
const defaultTaxRate = {{ (float) jv_tax_rate() }};
function formatMoney(amount) {
    return moneyCurrency + ' ' + Number(amount || 0).toLocaleString(undefined, {
        minimumFractionDigits: moneyDecimals,
        maximumFractionDigits: moneyDecimals
    });
}
function addItem() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr'); row.className = 'item-row';
    row.innerHTML = `<td><input type="text" name="items[${itemIndex}][description]" class="form-input" placeholder="Description" required></td><td><input type="number" name="items[${itemIndex}][quantity]" class="form-input qty" value="1" min="1" required onchange="calcRow(this)"></td><td><input type="number" name="items[${itemIndex}][unit_price]" class="form-input price" value="0" step="0.01" min="0" required onchange="calcRow(this)"></td><td><input type="number" name="items[${itemIndex}][tax_rate]" class="form-input tax" value="${defaultTaxRate}" step="0.01" min="0" max="100" onchange="calcRow(this)"></td><td><input type="text" class="form-input item-total" value="${formatMoney(0)}" readonly></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">✕</button></td>`;
    tbody.appendChild(row); itemIndex++;
}
function removeItem(btn) { if (document.querySelectorAll('.item-row').length > 1) { btn.closest('tr').remove(); calcTotals(); } }
function calcRow(el) {
    const row = el.closest('tr');
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    const taxRate = parseFloat(row.querySelector('.tax').value) || 0;
    row.querySelector('.item-total').value = formatMoney(qty * price * (1 + taxRate / 100));
    calcTotals();
}
function calcTotals() {
    let subtotal = 0, taxTotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        const taxRate = parseFloat(row.querySelector('.tax').value) || 0;
        subtotal += qty * price;
        taxTotal += (qty * price) * (taxRate / 100);
    });
    const discount = parseFloat(document.querySelector('[name="discount"]')?.value) || 0;
    document.getElementById('subtotalDisplay').textContent = formatMoney(subtotal);
    document.getElementById('taxDisplay').textContent = formatMoney(taxTotal);
    document.getElementById('totalDisplay').textContent = formatMoney(Math.max(0, subtotal - discount + taxTotal));
}
document.querySelectorAll('.item-row .qty, .item-row .price, .item-row .tax').forEach(input => calcRow(input));
document.querySelector('[name="discount"]')?.addEventListener('input', calcTotals);
</script>
@endpush
