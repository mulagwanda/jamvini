@extends('themes.default::layouts.admin')

@section('title', 'Social Calendar')
@section('breadcrumbs')<a href="{{ route('admin.social.index') }}">Social Centre</a> <span class="separator">/</span> <span class="current">Calendar</span>@endsection

@push('styles')
<style>
.calendar-hero { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:16px; align-items:center; margin-bottom:18px; }
.calendar-nav { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.calendar-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; margin-bottom:18px; }
.calendar-stat { border:1px solid var(--jv-gray-200); background:#fff; border-radius:10px; padding:13px 14px; }
.calendar-stat span { display:block; color:var(--jv-gray-500); font-size:.76rem; font-weight:800; text-transform:uppercase; }
.calendar-stat strong { display:block; color:var(--jv-gray-900); font-size:1.35rem; margin-top:3px; }
.calendar-layout { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:18px; align-items:start; }
.calendar-board { border:1px solid var(--jv-gray-200); background:#fff; border-radius:12px; overflow:hidden; }
.calendar-weekdays { display:grid; grid-template-columns:repeat(7,1fr); background:#f8fafc; border-bottom:1px solid var(--jv-gray-200); }
.calendar-weekdays div { padding:11px 12px; color:var(--jv-gray-500); font-size:.76rem; font-weight:900; text-transform:uppercase; }
.calendar-grid { display:grid; grid-template-columns:repeat(7,1fr); }
.calendar-day { min-height:148px; border-right:1px solid var(--jv-gray-100); border-bottom:1px solid var(--jv-gray-100); padding:9px; background:#fff; cursor:pointer; transition:.15s ease; }
.calendar-day:nth-child(7n) { border-right:0; }
.calendar-day:hover, .calendar-day.active { background:#f8fbff; box-shadow:inset 0 0 0 2px rgba(37,99,235,.18); }
.calendar-day.muted { background:#fbfdff; color:var(--jv-gray-400); }
.calendar-day.today .day-number { background:var(--jv-primary); color:#fff; }
.day-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:8px; }
.day-number { width:28px; height:28px; border-radius:50%; display:grid; place-items:center; font-size:.82rem; font-weight:900; color:var(--jv-gray-800); }
.day-count { color:var(--jv-gray-400); font-size:.72rem; font-weight:800; }
.calendar-events { display:grid; gap:6px; }
.calendar-event { border:1px solid var(--jv-gray-200); border-left:4px solid var(--jv-gray-400); border-radius:8px; padding:7px 8px; background:#fff; text-decoration:none; color:var(--jv-gray-800); display:block; overflow:hidden; }
.calendar-event strong { display:block; font-size:.76rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.calendar-event small { display:block; margin-top:3px; color:var(--jv-gray-500); font-size:.68rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.calendar-event.status-scheduled { border-left-color:#2563eb; background:#eff6ff; }
.calendar-event.status-published { border-left-color:#16a34a; background:#f0fdf4; }
.calendar-event.status-ready { border-left-color:#d97706; background:#fffbeb; }
.calendar-event.status-failed { border-left-color:#dc2626; background:#fef2f2; }
.calendar-event.status-draft { border-left-color:#64748b; background:#f8fafc; }
.calendar-more { color:var(--jv-gray-500); font-size:.72rem; font-weight:800; padding-left:3px; }
.calendar-side { display:grid; gap:14px; position:sticky; top:84px; }
.day-panel-empty { color:var(--jv-gray-500); line-height:1.5; }
.day-panel-list { display:grid; gap:10px; }
.day-panel-card { border:1px solid var(--jv-gray-200); border-radius:10px; padding:11px; background:#fff; }
.day-panel-card h4 { margin:0 0 5px; font-size:.92rem; }
.day-panel-meta { color:var(--jv-gray-500); font-size:.78rem; margin-bottom:8px; }
.platform-row { display:flex; flex-wrap:wrap; gap:5px; margin-top:8px; }
.platform-chip { border:1px solid var(--jv-gray-200); border-radius:999px; padding:3px 7px; color:var(--jv-gray-600); background:#fff; font-size:.68rem; font-weight:800; }
.status-key { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
.status-key span { display:inline-flex; align-items:center; gap:6px; color:var(--jv-gray-500); font-size:.75rem; font-weight:800; }
.status-dot { width:10px; height:10px; border-radius:50%; background:#64748b; }
.status-dot.scheduled { background:#2563eb; }
.status-dot.published { background:#16a34a; }
.status-dot.ready { background:#d97706; }
.status-dot.failed { background:#dc2626; }
.queue-card { border:1px solid var(--jv-gray-200); border-radius:10px; padding:10px; display:grid; gap:6px; }
.queue-card strong { font-size:.86rem; color:var(--jv-gray-900); }
.queue-card small { color:var(--jv-gray-500); }
@media (max-width:1180px) { .calendar-layout { grid-template-columns:1fr; } .calendar-side { position:static; } }
@media (max-width:760px) { .calendar-hero { grid-template-columns:1fr; } .calendar-stats { grid-template-columns:repeat(2,1fr); } .calendar-weekdays div { padding:8px 4px; text-align:center; } .calendar-day { min-height:116px; padding:6px; } .calendar-event strong { font-size:.68rem; } .calendar-event small { display:none; } }
</style>
@endpush

@section('content')
<div class="calendar-hero">
    <div>
        <h1 class="page-title">Social Calendar</h1>
        <p class="page-subtitle">Plan, review, and track scheduled social content for {{ $month->format('F Y') }}.</p>
    </div>
    <div class="calendar-nav">
        <a href="{{ route('admin.social.calendar', ['month' => $previousMonth]) }}" class="btn btn-outline-primary">Previous</a>
        <a href="{{ route('admin.social.calendar', ['month' => $currentMonth]) }}" class="btn btn-outline-primary">Today</a>
        <a href="{{ route('admin.social.calendar', ['month' => $nextMonth]) }}" class="btn btn-outline-primary">Next</a>
        <a href="{{ route('admin.social.posts.create') }}" class="btn btn-primary">New Post</a>
    </div>
</div>

<div class="calendar-stats">
    <div class="calendar-stat"><span>Scheduled</span><strong>{{ $stats['scheduled'] }}</strong></div>
    <div class="calendar-stat"><span>Published</span><strong>{{ $stats['published'] }}</strong></div>
    <div class="calendar-stat"><span>Ready Queue</span><strong>{{ $stats['ready'] }}</strong></div>
    <div class="calendar-stat"><span>Needs Attention</span><strong>{{ $stats['failed'] }}</strong></div>
</div>

<div class="calendar-layout">
    <div>
        <div class="status-key">
            <span><i class="status-dot scheduled"></i> Scheduled</span>
            <span><i class="status-dot published"></i> Published</span>
            <span><i class="status-dot ready"></i> Ready</span>
            <span><i class="status-dot failed"></i> Failed</span>
        </div>
        <div class="calendar-board">
            <div class="calendar-weekdays">
                @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $weekday)
                    <div>{{ $weekday }}</div>
                @endforeach
            </div>
            <div class="calendar-grid">
                @foreach($days as $day)
                    @php
                        $posts = $day['posts'];
                        $visible = $posts->take(3);
                    @endphp
                    <div class="calendar-day {{ $day['in_month'] ? '' : 'muted' }} {{ $day['date']->isToday() ? 'today' : '' }}"
                         data-day="{{ $day['key'] }}"
                         data-label="{{ $day['date']->format('l, M d, Y') }}">
                        <div class="day-head">
                            <span class="day-number">{{ $day['date']->format('j') }}</span>
                            @if($posts->count())
                                <span class="day-count">{{ $posts->count() }}</span>
                            @endif
                        </div>
                        <div class="calendar-events">
                            @foreach($visible as $post)
                                @php $eventDate = $post->scheduled_at ?: $post->published_at ?: $post->created_at; @endphp
                                <a href="{{ route('admin.social.posts.show', $post) }}"
                                   class="calendar-event status-{{ $post->status }}"
                                   data-event
                                   data-day="{{ $day['key'] }}"
                                   data-title="{{ $post->title }}"
                                   data-url="{{ route('admin.social.posts.show', $post) }}"
                                   data-edit-url="{{ route('admin.social.posts.edit', $post) }}"
                                   data-status="{{ ucfirst($post->status) }}"
                                   data-time="{{ $eventDate?->format('H:i') ?? '-' }}"
                                   data-campaign="{{ $post->campaign?->name ?? 'No campaign' }}"
                                   data-platforms="{{ implode(', ', array_map(fn ($p) => $platforms[$p] ?? $p, $post->platforms ?? [])) ?: '-' }}"
                                   data-caption="{{ str($post->caption)->limit(180) }}">
                                    <strong>{{ $eventDate?->format('H:i') ?? '--:--' }} {{ $post->title }}</strong>
                                    <small>{{ implode(', ', array_map(fn ($p) => $platforms[$p] ?? $p, $post->platforms ?? [])) ?: 'No platforms' }}</small>
                                </a>
                            @endforeach
                            @if($posts->count() > 3)
                                <div class="calendar-more">+{{ $posts->count() - 3 }} more</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <aside class="calendar-side">
        <div class="dash-card">
            <div class="dash-card-head"><h3 id="dayPanelTitle">Select a day</h3></div>
            <div id="dayPanelContent" class="day-panel-empty">Click any day on the calendar to see the posts planned for that date.</div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Ready Queue</h3></div>
            <div style="display:grid;gap:10px;">
                @forelse($readyPosts as $post)
                    <div class="queue-card">
                        <strong>{{ $post->title }}</strong>
                        <small>{{ ucfirst($post->status) }} - {{ $post->campaign?->name ?? 'No campaign' }}</small>
                        <div class="platform-row">
                            @forelse($post->platforms ?? [] as $platform)
                                <span class="platform-chip">{{ $platforms[$platform] ?? $platform }}</span>
                            @empty
                                <span class="platform-chip">No platforms</span>
                            @endforelse
                        </div>
                        <div style="display:flex;gap:8px;margin-top:6px;">
                            <a href="{{ route('admin.social.posts.edit', $post) }}" class="btn btn-sm btn-outline-primary">Schedule</a>
                            <a href="{{ route('admin.social.posts.show', $post) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                    </div>
                @empty
                    <p style="color:var(--jv-gray-500);margin:0;">No unscheduled ready posts.</p>
                @endforelse
            </div>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
const dayPanelTitle = document.getElementById('dayPanelTitle');
const dayPanelContent = document.getElementById('dayPanelContent');

document.querySelectorAll('.calendar-day').forEach(day => {
    day.addEventListener('click', event => {
        if (event.target.closest('a')) return;
        selectCalendarDay(day.dataset.day, day.dataset.label);
    });
});

document.querySelectorAll('[data-event]').forEach(eventLink => {
    eventLink.addEventListener('click', event => {
        if (event.metaKey || event.ctrlKey) return;
        event.preventDefault();
        selectCalendarDay(eventLink.dataset.day, eventLink.closest('.calendar-day')?.dataset.label || '');
    });
});

function selectCalendarDay(day, label) {
    document.querySelectorAll('.calendar-day').forEach(item => item.classList.toggle('active', item.dataset.day === day));
    const events = [...document.querySelectorAll(`[data-event][data-day="${cssEscape(day)}"]`)];
    dayPanelTitle.textContent = label || day;

    if (!events.length) {
        dayPanelContent.className = 'day-panel-empty';
        dayPanelContent.textContent = 'No posts planned for this day.';
        return;
    }

    dayPanelContent.className = 'day-panel-list';
    dayPanelContent.innerHTML = events.map(event => `
        <div class="day-panel-card">
            <h4>${escapeHtml(event.dataset.title)}</h4>
            <div class="day-panel-meta">${escapeHtml(event.dataset.time)} - ${escapeHtml(event.dataset.status)} - ${escapeHtml(event.dataset.campaign)}</div>
            <div>${escapeHtml(event.dataset.caption)}</div>
            <div class="platform-row">${platformChips(event.dataset.platforms)}</div>
            <div style="display:flex;gap:8px;margin-top:10px;">
                <a href="${escapeHtml(event.dataset.url)}" class="btn btn-sm btn-outline-primary">View</a>
                <a href="${escapeHtml(event.dataset.editUrl)}" class="btn btn-sm btn-primary">Edit</a>
            </div>
        </div>
    `).join('');
}

function platformChips(value) {
    return String(value || '-').split(',').map(item => item.trim()).filter(Boolean).map(item => `<span class="platform-chip">${escapeHtml(item)}</span>`).join('');
}

function cssEscape(value) {
    return String(value).replace(/["\\]/g, '\\$&');
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

const today = document.querySelector('.calendar-day.today') || document.querySelector('.calendar-day[data-day]');
if (today) selectCalendarDay(today.dataset.day, today.dataset.label);
</script>
@endpush
