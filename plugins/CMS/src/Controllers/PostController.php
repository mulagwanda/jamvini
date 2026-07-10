<?php

namespace Plugins\CMS\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\CMS\src\Models\Post;
use Plugins\CMS\src\Models\Category;
use Plugins\Media\src\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('categories')->latest()->paginate(15);
        return view('plugins.CMS::admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::where('type', 'post')->get();
        $media = Media::where('mime_type', 'like', 'image/%')->latest()->limit(24)->get();
        return view('plugins.CMS::admin.posts.create', compact('categories', 'media'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:cms_categories,id',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);

        $categoryIds = $validated['categories'] ?? [];
        unset($validated['categories']);

        $validated['slug'] = $this->uniqueSlug($request->filled('slug') ? $validated['slug'] : $validated['title']);
        $validated['author_id'] = auth('admin')->id();
        $validated['published_at'] = $validated['status'] === 'published'
            ? ($validated['published_at'] ?? now())
            : null;

        $post = Post::create($validated);
        
        if (!empty($categoryIds)) {
            $post->categories()->sync($categoryIds);
        }

        return redirect()->route('admin.cms.posts.index')->with('success', 'Post created!');
    }

    public function edit(Post $post)
    {
        $categories = Category::where('type', 'post')->get();
        $media = Media::where('mime_type', 'like', 'image/%')->latest()->limit(24)->get();
        return view('plugins.CMS::admin.posts.edit', compact('post', 'categories', 'media'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:cms_categories,id',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);

        $categoryIds = $validated['categories'] ?? [];
        unset($validated['categories']);

        $validated['slug'] = $this->uniqueSlug($request->filled('slug') ? $validated['slug'] : $validated['title'], $post->id);
        $validated['published_at'] = $validated['status'] === 'published'
            ? ($validated['published_at'] ?? $post->published_at ?? now())
            : null;

        $post->update($validated);
        $post->categories()->sync($categoryIds);

        return redirect()->route('admin.cms.posts.index')->with('success', 'Post updated!');
    }

    public function preview(Post $post)
    {
        $post->load('categories');
        return view($this->themeView('post'), compact('post'));
    }

    protected function themeView(string $view): string
    {
        $theme = active_theme('public');
        $candidate = "themes.{$theme}::{$view}";

        return View::exists($candidate) ? $candidate : "themes.default::{$view}";
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('admin.cms.posts.index')->with('success', 'Post deleted!');
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'post';
        $slug = $base;
        $count = 2;

        while (
            Post::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
