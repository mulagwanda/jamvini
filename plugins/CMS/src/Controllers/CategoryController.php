<?php

namespace Plugins\CMS\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\CMS\src\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->paginate(15);
        return view('plugins.CMS::admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->get();
        return view('plugins.CMS::admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:post,page',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:cms_categories,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        Category::create($validated);

        return redirect()->route('admin.cms.categories.index')->with('success', 'Category created!');
    }

    public function edit(Category $category)
    {
        $parents = Category::where('id', '!=', $category->id)->whereNull('parent_id')->get();
        return view('plugins.CMS::admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:post,page',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:cms_categories,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category->update($validated);

        return redirect()->route('admin.cms.categories.index')->with('success', 'Category updated!');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.cms.categories.index')->with('success', 'Category deleted!');
    }
}