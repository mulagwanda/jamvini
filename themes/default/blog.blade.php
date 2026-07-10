@extends('themes.default::layouts.frontend')

@section('title', 'Blog — ' . \App\Models\Setting::get('company_name', 'JamVini Hosting'))

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / Blog</div>
        <h1>Stories from the cloud</h1>
        <p>Performance, security & developer culture.</p>
    </div>
</section>

<section>
    <div class="container">
        <div class="blog-grid-wrap">
            <div class="blog-grid">
                @forelse($posts as $post)
                    <article class="blog-card reveal">
                        @if($post->featured_image)
                            <img src="{{ Str::startsWith($post->featured_image, ['http://', 'https://', '/']) ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="blog-thumb" style="object-fit:cover;">
                        @else
                            <div class="blog-thumb">{{ strtoupper(substr($post->title, 0, 2)) }}</div>
                        @endif
                        <div class="blog-body">
                            <div class="blog-meta">
                                {{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}
                                @if($post->categories->count() > 0)
                                    · {{ $post->categories->first()->name }}
                                @endif
                            </div>
                            <h3>{{ $post->title }}</h3>
                            <p>{{ Str::limit($post->excerpt ?? strip_tags($post->content), 120) }}</p>
                            <a href="{{ route('blog.post', $post->slug) }}" class="read-more">Read more →</a>
                        </div>
                    </article>
                @empty
                    <div class="blog-card reveal" style="grid-column: 1/-1; text-align: center; padding: 60px;">
                        <h3>No posts yet</h3>
                        <p>Check back soon for articles on hosting, performance, and security.</p>
                    </div>
                @endforelse
            </div>
            
            <aside>
                @if(class_exists(\Plugins\CMS\src\Models\Category::class))
                    @php $categories = \Plugins\CMS\src\Models\Category::where('type', 'post')->get(); @endphp
                    @if($categories->count() > 0)
                    <div class="sidebar-card reveal">
                        <h4>Categories</h4>
                        <ul>
                            @foreach($categories as $cat)
                                <li><a href="#">{{ $cat->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                @endif
                
                @php $recentPosts = \Plugins\CMS\src\Models\Post::published()->latest()->limit(5)->get(); @endphp
                @if($recentPosts->count() > 0)
                <div class="sidebar-card reveal">
                    <h4>Recent posts</h4>
                    <ul>
                        @foreach($recentPosts as $rp)
                            <li><a href="{{ route('blog.post', $rp->slug) }}">{{ $rp->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </aside>
        </div>
        
        {{ $posts->links() }}
    </div>
</section>
@endsection
