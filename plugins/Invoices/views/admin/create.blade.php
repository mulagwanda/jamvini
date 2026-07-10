@extends('themes.default::layouts.admin')

@section('title', 'Create Invoice')
@section('breadcrumbs')<a href="{{ route('admin.invoices.index') }}">Invoices</a> <span class="separator">/</span> <span class="current">Create</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Create Invoice</h1>
    <p class="page-subtitle">Generate a new invoice for a client</p>
</div>

<form action="{{ route('admin.invoices.store') }}" method="POST" id="invoiceForm">
    @csrf
    
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>📄 Invoice Details</h3></div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Client *</label>
                <select name="client_id" class="form-select" required>
                    <option value="">Select client...</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ (string) old('client_id', request('client_id')) === (string) $c->id ? 'selected' : '' }}>{{ $c->full_name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Invoice Number</label>
                <input type="text" class="form-input" value="{{ $invoiceNumber }}" readonly>
                <input type="hidden" name="invoice_number" value="{{ $invoiceNumber }}">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
            <div class="form-group">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-input" value="{{ old('due_date', $dueDate) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                </select>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 120px 1fr 1fr 1fr; gap: 16px; margin-top: 16px;">
            <div class="form-group">
                <label class="form-label">Currency</label>
                <input type="text" name="currency" class="form-input" value="{{ old('currency', \App\Models\Setting::get('currency', 'TZS')) }}" maxlength="3">
            </div>
            <div class="form-group">
                <label class="form-label">Discount</label>
                <input type="number" name="discount" class="form-input" value="{{ old('discount', 0) }}" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Source</label>
                <input type="text" name="source" class="form-input" value="{{ old('source', 'admin') }}" placeholder="admin, WHMCS, import">
            </div>
            <div class="form-group">
                <label class="form-label">External / WHMCS ID</label>
                <input type="text" name="external_id" class="form-input" value="{{ old('external_id') }}" placeholder="Optional">
            </div>
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
                    @foreach(old('items', [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => $defaultTaxRate]]) as $i => $item)
                    <tr class="item-row">
                        <td><input type="text" name="items[{{ $i }}][description]" class="form-input" value="{{ $item['description'] }}" placeholder="Service description" required></td>
                        <td><input type="number" name="items[{{ $i }}][quantity]" class="form-input qty" value="{{ $item['quantity'] }}" min="1" required onchange="calcRow(this)"></td>
                        <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-input price" value="{{ $item['unit_price'] }}" step="0.01" min="0" required onchange="calcRow(this)"></td>
                        <td><input type="number" name="items[{{ $i }}][tax_rate]" class="form-input tax" value="{{ $item['tax_rate'] }}" step="0.01" min="0" max="100" onchange="calcRow(this)"></td>
                        <td><input type="text" class="form-input item-total" value="0.00" readonly></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">✕</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
            <div style="width: 280px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0;"><span>Subtotal:</span><strong id="subtotalDisplay">{{ jv_format_money(0) }}</strong></div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;"><span>{{ jv_tax_label() }}:</span><strong id="taxDisplay">{{ jv_format_money(0) }}</strong></div>
                <div style="display: flex; justify-content: space-between; padding: 12px 0; border-top: 2px solid var(--jv-gray-200); font-size: 1.1rem;"><span>Total:</span><strong id="totalDisplay">{{ jv_format_money(0) }}</strong></div>
            </div>
        </div>
    </div>

    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>📝 Notes</h3></div>
        <textarea name="notes" class="form-textarea" rows="2" placeholder="Payment instructions or notes...">{{ old('notes') }}</textarea>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px;">
            <textarea name="payment_terms" class="form-textarea" rows="3" placeholder="Payment terms shown to client...">{{ old('payment_terms', \App\Models\Setting::get('invoice_payment_terms', '')) }}</textarea>
            <textarea name="admin_notes" class="form-textarea" rows="3" placeholder="Private billing notes...">{{ old('admin_notes') }}</textarea>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">✅ Create Invoice</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
let itemIndex = {{ count(old('items', [1])) }};
const moneyCurrency = @json(\App\Models\Setting::get('currency', 'TZS'));
const moneyDecimals = {{ (int) \App\Models\Setting::get('currency_decimal_places', \App\Models\Setting::get('currency', 'TZS') === 'TZS' ? '0' : '2') }};
const defaultTaxRate = {{ (float) $defaultTaxRate }};
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
