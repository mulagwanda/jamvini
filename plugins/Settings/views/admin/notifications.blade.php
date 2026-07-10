@extends('themes.default::layouts.admin')

@section('title', 'Notification Templates')
@section('breadcrumbs')<span class="current">Notification Templates</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Notification Templates</h1>
    <p class="page-subtitle">Manage email and SMS notification templates</p>
</div>

<div class="dash-card" style="padding: 0; overflow: hidden;">
    <table class="table" style="margin: 0;">
        <thead><tr><th>Template</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach(\DB::table('notification_templates')->get() as $template)
            <tr>
                <td>
                    <strong>{{ $template->name }}</strong>
                    <div style="font-size: 0.8rem; color: var(--jv-gray-500);">{{ $template->slug }}</div>
                </td>
                <td><span class="pill pill-info">{{ ucfirst($template->type) }}</span></td>
                <td><span class="pill pill-{{ $template->is_active ? 'ok' : 'mute' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editTemplate({{ $template->id }})">✏️ Edit</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Edit Modal --}}
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;" onclick="if(event.target === this) this.style.display='none'">
    <div style="background: #fff; border-radius: 18px; padding: 2rem; max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation()">
        <h3 style="margin-bottom: 1rem;">Edit Template</h3>
        <form id="editForm" method="POST" action="{{ route('admin.settings.notifications.update') }}">
            @csrf
            <input type="hidden" name="template_id" id="editTemplateId">
            <div class="form-group"><label class="form-label">Subject</label><input type="text" name="subject" id="editSubject" class="form-input"></div>
            <div class="form-group"><label class="form-label">Body</label><textarea name="body" id="editBody" class="form-textarea" rows="12"></textarea></div>
            <div class="form-group"><label class="toggle-switch"><input type="checkbox" name="is_active" id="editActive" value="1"><span class="toggle-slider"></span><span>Active</span></label></div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">💾 Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const templates = @json(\DB::table('notification_templates')->get());

function editTemplate(id) {
    const t = templates.find(t => t.id === id);
    if (!t) return;
    document.getElementById('editTemplateId').value = t.id;
    document.getElementById('editSubject').value = t.subject || '';
    document.getElementById('editBody').value = t.body || '';
    document.getElementById('editActive').checked = t.is_active;
    document.getElementById('editModal').style.display = 'flex';
}
</script>
@endpush