<?php

namespace Plugins\BannerStudio\src\Controllers;

use App\Core\Hooks\Action;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Plugins\BannerStudio\src\Models\Banner;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::latest()->paginate(15);

        return view('plugins.BannerStudio::admin.index', compact('banners'));
    }

    public function create()
    {
        return view('plugins.BannerStudio::admin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $banner = Banner::create([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'is_active' => $request->boolean('is_active', true),
            'settings' => $this->defaultSettings(),
            'layers' => $this->defaultLayers($validated['title']),
        ]);

        return redirect()->route('admin.banner-studio.studio', $banner)
            ->with('success', 'Banner created. Start designing.');
    }

    public function edit(Banner $banner)
    {
        return view('plugins.BannerStudio::admin.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $banner->update([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title'], $banner->id),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.banner-studio.index')
            ->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return redirect()->route('admin.banner-studio.index')
            ->with('success', 'Banner deleted.');
    }

    public function studio(Banner $banner)
    {
        if (empty($banner->layers)) {
            $banner->update(['layers' => $this->defaultLayers($banner->title)]);
        }

        return view('plugins.BannerStudio::admin.studio', compact('banner'));
    }

    public function saveStudio(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'layers' => ['required', 'array'],
            'layers.*.id' => ['required', 'string', 'max:100'],
            'layers.*.type' => ['required', 'string', 'max:30'],
        ]);

        $settings = array_merge($this->defaultSettings(), $validated['settings']);

        $banner->update([
            'settings' => $settings,
            'layers' => $validated['layers'],
        ]);

        Action::do('banner_studio.saved', $banner);

        return response()->json(['success' => true, 'banner' => $banner->fresh()]);
    }

    public function render(string $slug)
    {
        $banner = Banner::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('plugins.BannerStudio::public.render', compact('banner'));
    }

    protected function defaultSettings(): array
    {
        return [
            'height' => 620,
            'radius' => 0,
            'backgroundType' => 'gradient',
            'backgroundColor' => '#0f172a',
            'backgroundGradient' => 'linear-gradient(135deg, #0f172a 0%, #214f54 48%, #7a5cff 100%)',
            'backgroundImage' => '',
            'backgroundPosition' => 'center center',
            'overlay' => 'rgba(15,23,42,.35)',
        ];
    }

    protected function defaultLayers(string $title): array
    {
        return [
            [
                'id' => 'eyebrow-' . Str::random(6),
                'type' => 'text',
                'name' => 'Eyebrow',
                'content' => 'JamVini Hosting',
                'x' => 9,
                'y' => 22,
                'width' => 34,
                'height' => 7,
                'style' => ['fontSize' => 14, 'color' => '#a7f3d0', 'fontWeight' => 800, 'align' => 'left', 'letterSpacing' => 0],
            ],
            [
                'id' => 'heading-' . Str::random(6),
                'type' => 'heading',
                'name' => 'Heading',
                'content' => $title,
                'x' => 9,
                'y' => 31,
                'width' => 58,
                'height' => 20,
                'style' => ['fontSize' => 58, 'color' => '#ffffff', 'fontWeight' => 900, 'align' => 'left', 'letterSpacing' => 0],
            ],
            [
                'id' => 'text-' . Str::random(6),
                'type' => 'text',
                'name' => 'Subtitle',
                'content' => 'Design a hero banner with text, images, buttons, shapes, and domain search.',
                'x' => 9,
                'y' => 55,
                'width' => 48,
                'height' => 12,
                'style' => ['fontSize' => 19, 'color' => '#dbeafe', 'fontWeight' => 400, 'align' => 'left', 'letterSpacing' => 0],
            ],
            [
                'id' => 'button-' . Str::random(6),
                'type' => 'button',
                'name' => 'Primary Button',
                'content' => 'Get Started',
                'link' => '/hosting',
                'target' => '_self',
                'x' => 9,
                'y' => 72,
                'width' => 16,
                'height' => 8,
                'style' => ['fontSize' => 16, 'color' => '#0f172a', 'background' => '#ffffff', 'fontWeight' => 800, 'align' => 'center', 'radius' => 8, 'letterSpacing' => 0],
            ],
        ];
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'banner';
        $slug = $base;
        $count = 2;

        while (Banner::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
