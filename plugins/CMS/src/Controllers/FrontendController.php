<?php

namespace Plugins\CMS\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\CMS\src\Models\Page;
use Plugins\CMS\src\Models\Post;
use App\Models\Setting;

class FrontendController extends Controller
{
    public function homepage()
    {
        $type = Setting::get('homepage_type', 'landing');

        if ($type === 'page') {
            return $this->showPage();
        }

        if ($type === 'posts') {
            return $this->showPosts();
        }

        // Default landing page
        return view('welcome');
    }

    protected function showPage()
    {
        $pageId = Setting::get('homepage_page_id');
        $page = Page::published()->find($pageId);
        
        if (!$page) return view('welcome');

        return view(jv_theme_view('homepage'), compact('page'));
    }

    protected function showPosts()
    {
        $posts = Post::published()->latest()->paginate(10);
        return view(jv_theme_view('blog'), compact('posts'));
    }

    public function show(string $slug)
    {
        $page = \Plugins\CMS\src\Models\Page::published()->where('slug', $slug)->first();
        
        if (!$page) {
            abort(404);
        }

        // If page has builder blocks, render with frontend layout
        if (!empty($page->blocks)) {
            return view(jv_theme_view('homepage'), compact('page'));
        }

        // Fallback: simple content page
        return view(jv_theme_view('page'), compact('page'));
    }

    public function blog()
    {
        $posts = \Plugins\CMS\src\Models\Post::published()
            ->with('categories')
            ->latest()
            ->paginate(9);
        
        return view(jv_theme_view('blog'), compact('posts'));
    }

    public function post(string $slug)
    {
        $post = \Plugins\CMS\src\Models\Post::published()
            ->with('categories')
            ->where('slug', $slug)
            ->firstOrFail();
        
        return view(jv_theme_view('post'), compact('post'));
    }
}
