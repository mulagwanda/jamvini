<?php

namespace Plugins\JvBuilder\src\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plugins\CMS\src\Models\Page;

class BuilderController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->paginate(15);

        return view('plugins.JvBuilder::admin.index', compact('pages'));
    }

    public function edit(Page $page)
    {
        return view('plugins.JvBuilder::admin.builder', compact('page'));
    }

    public function save(Request $request, Page $page)
    {
        $validated = $request->validate([
            'blocks' => ['nullable', 'array'],
            'html' => ['nullable', 'string'],
            'css' => ['nullable', 'string'],
        ]);

        $page->update([
            'blocks' => $validated['blocks'] ?? [],
            'html' => $validated['html'] ?? $page->html,
            'css' => $validated['css'] ?? $page->css,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page saved.',
            'blocks' => $page->fresh()->blocks,
        ]);
    }
}
