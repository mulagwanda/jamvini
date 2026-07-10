@extends('themes.default::layouts.frontend')

@section('title', $announcement->title)

@section('content')
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="/announcements">Announcements</a> / {{ $announcement->title }}</div><h1>{{ $announcement->title }}</h1><p>{{ $announcement->published_at?->format('M d, Y') }}</p></div></section>
<main class="container" style="padding:2rem 0;max-width:860px;">
    <article style="background:#fff;border:1px solid var(--gray-200);border-radius:16px;padding:2rem;line-height:1.75;">
        <span class="pill pill-info">{{ ucfirst($announcement->type) }}</span>
        <div style="white-space:pre-wrap;margin-top:1rem;">{{ $announcement->content }}</div>
    </article>
</main>
@endsection
