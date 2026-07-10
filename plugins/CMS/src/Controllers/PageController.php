<?php

namespace Plugins\CMS\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\CMS\src\Models\Page;
use Plugins\Media\src\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->paginate(15);
        return view('plugins.CMS::admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $media = Media::where('mime_type', 'like', 'image/%')->latest()->limit(24)->get();
        return view('plugins.CMS::admin.pages.create', compact('media'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'template' => 'nullable|string',
            'featured_image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = $this->uniqueSlug($request->filled('slug') ? $validated['slug'] : $validated['title']);
        $validated['author_id'] = auth('admin')->id();

        Page::create($validated);

        return redirect()->route('admin.cms.pages.index')->with('success', 'Page created!');
    }

    public function edit(Page $page)
    {
        $media = Media::where('mime_type', 'like', 'image/%')->latest()->limit(24)->get();
        return view('plugins.CMS::admin.pages.edit', compact('page', 'media'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'template' => 'nullable|string',
            'featured_image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = $this->uniqueSlug($request->filled('slug') ? $validated['slug'] : $validated['title'], $page->id);
        $page->update($validated);

        return redirect()->route('admin.cms.pages.index')->with('success', 'Page updated!');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.cms.pages.index')->with('success', 'Page deleted!');
    }

    public function preview(Page $page)
    {
        return !empty($page->blocks)
            ? view($this->themeView('homepage'), compact('page'))
            : view($this->themeView('page'), compact('page'));
    }

    protected function themeView(string $view): string
    {
        $theme = active_theme('public');
        $candidate = "themes.{$theme}::{$view}";

        return View::exists($candidate) ? $candidate : "themes.default::{$view}";
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'page';
        $slug = $base;
        $count = 2;

        while (
            Page::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
