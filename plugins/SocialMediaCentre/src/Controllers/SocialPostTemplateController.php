<?php

namespace Plugins\SocialMediaCentre\src\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\SocialMediaCentre\src\Models\SocialPostTemplate;

class SocialPostTemplateController extends Controller
{
    protected array $platforms = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'x' => 'X',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
    ];

    protected array $categories = [
        'offers' => 'Offers',
        'announcements' => 'Announcements',
        'education' => 'Education',
        'community' => 'Community',
        'customer-care' => 'Customer Care',
        'general' => 'General',
    ];

    public function index(Request $request)
    {
        if (!Schema::hasTable('social_post_templates')) {
            return view('plugins.SocialMediaCentre::admin.templates.missing-table');
        }

        $templates = SocialPostTemplate::query()
            ->when($request->category, fn ($query, $category) => $query->where('category', $category))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(18);

        return view('plugins.SocialMediaCentre::admin.templates.index', [
            'templates' => $templates,
            'categories' => $this->categories,
            'platforms' => $this->platforms,
        ]);
    }

    public function create()
    {
        return view('plugins.SocialMediaCentre::admin.templates.form', [
            'template' => new SocialPostTemplate(['status' => 'active', 'category' => 'general']),
            'categories' => $this->categories,
            'platforms' => $this->platforms,
        ]);
    }

    public function store(Request $request)
    {
        SocialPostTemplate::create($this->data($request));

        return redirect()->route('admin.social.templates.index')->with('success', 'Post template created.');
    }

    public function edit(SocialPostTemplate $template)
    {
        return view('plugins.SocialMediaCentre::admin.templates.form', [
            'template' => $template,
            'categories' => $this->categories,
            'platforms' => $this->platforms,
        ]);
    }

    public function update(Request $request, SocialPostTemplate $template)
    {
        $template->update($this->data($request, $template));

        return redirect()->route('admin.social.templates.index')->with('success', 'Post template updated.');
    }

    public function destroy(SocialPostTemplate $template)
    {
        $template->delete();

        return back()->with('success', 'Post template deleted.');
    }

    public function use(SocialPostTemplate $template)
    {
        abort_unless($template->status === 'active', 404);

        return view('plugins.SocialMediaCentre::admin.templates.use', [
            'template' => $template,
            'variables' => $this->variables($template),
            'platforms' => $this->platforms,
            'preview' => [
                'title' => $this->replaceVariables($template->title_template, []),
                'caption' => $this->replaceVariables($template->caption_template, []),
            ],
        ]);
    }

    public function compose(Request $request, SocialPostTemplate $template)
    {
        abort_unless($template->status === 'active', 404);

        $variables = $this->variables($template);
        $rules = [];
        foreach ($variables as $variable) {
            $rules["variables.{$variable}"] = 'nullable|string|max:500';
        }

        $validated = $request->validate($rules);
        $values = $validated['variables'] ?? [];

        return redirect()->route('admin.social.posts.create', [
            'template' => $template->id,
            'values' => $values,
        ]);
    }

    protected function data(Request $request, ?SocialPostTemplate $template = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:80',
            'description' => 'nullable|string|max:500',
            'title_template' => 'required|string|max:255',
            'caption_template' => 'required|string|max:5000',
            'hashtags' => 'nullable|string|max:1000',
            'platforms' => 'nullable|array',
            'platforms.*' => 'string|max:40',
            'status' => 'required|in:active,draft,archived',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $slug = Str::slug($validated['name']);
        $base = $slug;
        $i = 2;
        while (SocialPostTemplate::where('slug', $slug)->when($template, fn ($q) => $q->whereKeyNot($template->id))->exists()) {
            $slug = $base . '-' . $i++;
        }

        $hashtags = collect(preg_split('/[\s,]+/', $validated['hashtags'] ?? ''))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->map(fn ($tag) => str_starts_with($tag, '#') ? $tag : '#' . $tag)
            ->unique()
            ->values()
            ->all();

        return [
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'title_template' => $validated['title_template'],
            'caption_template' => $validated['caption_template'],
            'hashtags' => $hashtags,
            'platforms' => array_values(array_intersect($validated['platforms'] ?? [], array_keys($this->platforms))),
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ];
    }

    protected function variables(SocialPostTemplate $template): array
    {
        preg_match_all('/{{\s*([a-zA-Z0-9_]+)\s*}}/', $template->title_template . "\n" . $template->caption_template, $matches);

        return collect($matches[1] ?? [])
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function replaceVariables(string $value, array $variables): string
    {
        return preg_replace_callback('/{{\s*([a-zA-Z0-9_]+)\s*}}/', function ($matches) use ($variables) {
            $key = $matches[1];
            $fallback = '[' . str_replace('_', ' ', $key) . ']';

            return filled($variables[$key] ?? null) ? $variables[$key] : $fallback;
        }, $value);
    }
}
