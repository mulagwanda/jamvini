<?php

namespace Plugins\KnowledgeBase\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\KnowledgeBase\src\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function publicIndex()
    {
        $articles = Article::published()->latest()->paginate(12);

        return view('plugins.knowledge-base::public.index', compact('articles'));
    }

    public function publicShow(string $slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();
        $article->increment('views');

        return view('plugins.knowledge-base::public.show', compact('article'));
    }

    public function index()
    {
        $articles = Article::latest()->paginate(15);
        return view('plugins.knowledge-base::admin.index', compact('articles'));
    }

    public function create()
    {
        return view('plugins.knowledge-base::admin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'is_published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        Article::create($validated);

        return redirect()->route('admin.kb.index')
            ->with('success', 'Article created!');
    }

    public function edit(Article $article)
    {
        return view('plugins.knowledge-base::admin.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'is_published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $article->update($validated);

        return redirect()->route('admin.kb.index')
            ->with('success', 'Article updated!');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->route('admin.kb.index')
            ->with('success', 'Article deleted!');
    }
}
