@extends('themes.default::layouts.frontend')

@section('title', 'Announcements')

@section('content')
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="/">Home</a> / Announcements</div><h1>Announcements</h1><p>News, maintenance updates, and service notices.</p></div></section>
<main class="container" style="padding:2rem 0;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;">
        @forelse($announcements as $announcement)
            <a href="{{ route('support.announcements.show', $announcement) }}" style="background:#fff;border:1px solid var(--gray-200);border-radius:16px;padding:1.25rem;color:inherit;">
                <span class="pill pill-info">{{ ucfirst($announcement->type) }}</span>
                <h3>{{ $announcement->title }}</h3>
                <p style="color:var(--gray-600);">{{ $announcement->summary ?: str($announcement->content)->limit(140) }}</p>
                <small>{{ $announcement->published_at?->format('M d, Y') }}</small>
            </a>
        @empty
            <p style="color:var(--gray-500);">No announcements yet.</p>
        @endforelse
    </div>
    <div style="margin-top:1rem;">{{ $announcements->links() }}</div>
</main>
@endsection
