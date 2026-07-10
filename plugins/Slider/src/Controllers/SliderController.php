<?php

namespace Plugins\Slider\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Slider\src\Models\Slider;
use Plugins\Slider\src\Models\Slide;
use Plugins\Slider\Plugin as SliderPlugin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::withCount('slides')->latest()->paginate(15);
        return view('plugins.Slider::admin.index', compact('sliders'));
    }

    public function create()
    {
        return view('plugins.Slider::admin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:hero,carousel,testimonial',
            'settings' => 'nullable|array',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['title']);
        $validated['settings'] = $this->sliderSettings($request);
        $validated['is_active'] = $request->boolean('is_active', true);

        $slider = Slider::create($validated);

        return redirect()->route('admin.slider.edit', $slider)->with('success', 'Slider created. Add your first slide.');
    }

    public function edit(Slider $slider)
    {
        $slider->load('slides');
        return view('plugins.Slider::admin.edit', compact('slider'));
    }

    public function studio(Slider $slider)
    {
        SliderPlugin::publishAssets();

        $slider->load('slides');

        if ($slider->slides->isEmpty()) {
            $slider->slides()->create([
                'title' => 'New Slide',
                'subtitle' => 'Welcome to Slider Studio',
                'description' => 'Design this slide with layers.',
                'order' => 0,
                'is_active' => true,
                'layers' => $this->defaultLayers('New Slide'),
            ]);

            $slider->load('slides');
        }

        return view('plugins.Slider::admin.studio.index', [
            'slider' => $slider,
            'slides' => $slider->slides,
            'studioCssUrl' => asset('plugins/slider/css/studio.css') . '?v=' . @filemtime(public_path('plugins/slider/css/studio.css')),
            'studioJsUrl' => asset('plugins/slider/js/studio.js') . '?v=' . @filemtime(public_path('plugins/slider/js/studio.js')),
        ]);
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:hero,carousel,testimonial',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['title'], $slider->id);
        $validated['settings'] = $this->sliderSettings($request);
        $validated['is_active'] = $request->boolean('is_active');

        $slider->update($validated);

        return redirect()->route('admin.slider.index')->with('success', 'Slider updated!');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();
        return redirect()->route('admin.slider.index')->with('success', 'Slider deleted!');
    }

    // Slide management
    public function addSlide(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|string|max:500',
            'button2_text' => 'nullable|string|max:50',
            'button2_link' => 'nullable|string|max:500',
            'alignment' => 'nullable|in:left,center,right',
            'overlay_color' => 'nullable|string|max:80',
            'text_color' => 'nullable|string|max:30',
            'background_position' => 'nullable|string|max:80',
            'content_width' => 'nullable|string|max:30',
            'animation' => 'nullable|in:fade-up,fade,slide-left,zoom',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['slider_id'] = $slider->id;
        $validated['order'] = $validated['order'] ?? $slider->slides()->count();
        $validated['is_active'] = $request->boolean('is_active', true);

        Slide::create($validated);

        return redirect()->route('admin.slider.edit', $slider)->with('success', 'Slide added!');
    }

    public function updateSlide(Request $request, Slider $slider, Slide $slide)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|string|max:500',
            'button2_text' => 'nullable|string|max:50',
            'button2_link' => 'nullable|string|max:500',
            'alignment' => 'nullable|in:left,center,right',
            'overlay_color' => 'nullable|string|max:80',
            'text_color' => 'nullable|string|max:30',
            'background_position' => 'nullable|string|max:80',
            'content_width' => 'nullable|string|max:30',
            'animation' => 'nullable|in:fade-up,fade,slide-left,zoom',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $slide->update($validated);

        return redirect()->route('admin.slider.edit', $slider)->with('success', 'Slide updated!');
    }

    public function deleteSlide(Slider $slider, Slide $slide)
    {
        $slide->delete();
        return redirect()->route('admin.slider.edit', $slider)->with('success', 'Slide deleted!');
    }

    public function studioSettings(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $slider->update([
            'settings' => array_merge($slider->settings ?? [], $validated['settings']),
        ]);

        return response()->json(['success' => true, 'settings' => $slider->fresh()->settings]);
    }

    public function studioSlide(Slider $slider)
    {
        $order = $slider->slides()->count();
        $slide = $slider->slides()->create([
            'title' => 'Slide ' . ($order + 1),
            'subtitle' => 'New slide',
            'description' => 'Design this slide with layers.',
            'order' => $order,
            'is_active' => true,
            'layers' => $this->defaultLayers('Slide ' . ($order + 1)),
        ]);

        return response()->json([
            'success' => true,
            'slide' => [
                'id' => $slide->id,
                'name' => $slide->title,
                'saveUrl' => route('admin.slider.slides.layers', [$slider, $slide]),
                'background' => [
                    'image' => $slide->image,
                    'overlay' => $slide->overlay_color ?: 'rgba(15,23,42,.58)',
                    'position' => $slide->background_position ?: 'center center',
                ],
                'layers' => $slide->layers ?: [],
            ],
        ]);
    }

    public function saveLayers(Request $request, Slider $slider, Slide $slide)
    {
        abort_unless((int) $slide->slider_id === (int) $slider->id, 404);

        $validated = $request->validate([
            'layers' => ['required', 'array'],
            'layers.*.id' => ['required', 'string', 'max:80'],
            'layers.*.type' => ['required', 'string', 'max:30'],
            'background' => ['nullable', 'array'],
        ]);

        $slide->update([
            'layers' => $validated['layers'],
            'image' => $validated['background']['image'] ?? $slide->image,
            'overlay_color' => $validated['background']['overlay'] ?? $slide->overlay_color,
            'background_position' => $validated['background']['position'] ?? $slide->background_position,
        ]);

        return response()->json(['success' => true, 'layers' => $slide->fresh()->layers]);
    }

    // Public render
    public function render(string $slug)
    {
        $slider = Slider::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $slider->load('activeSlides');
        return view('plugins.Slider::public.render', compact('slider'));
    }

    protected function sliderSettings(Request $request): array
    {
        $settings = $request->input('settings', []);

        return [
            'autoplay' => $request->boolean('settings.autoplay'),
            'pause_on_hover' => $request->boolean('settings.pause_on_hover', true),
            'navigation' => $request->boolean('settings.navigation', true),
            'pagination' => $request->boolean('settings.pagination', true),
            'keyboard' => $request->boolean('settings.keyboard', true),
            'loop' => $request->boolean('settings.loop', true),
            'speed' => max(150, min(3000, (int) ($settings['speed'] ?? 700))),
            'delay' => max(1000, min(20000, (int) ($settings['delay'] ?? 5500))),
            'effect' => in_array(($settings['effect'] ?? 'fade'), ['fade', 'slide'], true) ? $settings['effect'] : 'fade',
            'height' => max(260, min(980, (int) ($settings['height'] ?? 620))),
            'radius' => max(0, min(40, (int) ($settings['radius'] ?? 0))),
            'theme' => in_array(($settings['theme'] ?? 'dark'), ['dark', 'light'], true) ? ($settings['theme'] ?? 'dark') : 'dark',
        ];
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'slider';
        $slug = $base;
        $count = 2;

        while (
            Slider::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    protected function defaultLayers(string $title): array
    {
        return [
            [
                'id' => 'layer-heading-' . Str::random(6),
                'type' => 'heading',
                'name' => 'Heading',
                'content' => $title,
                'x' => 9,
                'y' => 28,
                'width' => 56,
                'height' => 16,
                'style' => ['fontSize' => 60, 'color' => '#ffffff', 'fontWeight' => 800, 'align' => 'left'],
                'link' => null,
            ],
            [
                'id' => 'layer-text-' . Str::random(6),
                'type' => 'text',
                'name' => 'Text',
                'content' => 'Add a supporting message for this slide.',
                'x' => 9,
                'y' => 48,
                'width' => 48,
                'height' => 12,
                'style' => ['fontSize' => 20, 'color' => '#dbeafe', 'fontWeight' => 400, 'align' => 'left'],
                'link' => null,
            ],
            [
                'id' => 'layer-button-' . Str::random(6),
                'type' => 'button',
                'name' => 'Button',
                'content' => 'Get Started',
                'x' => 9,
                'y' => 65,
                'width' => 16,
                'height' => 8,
                'style' => ['fontSize' => 16, 'color' => '#0f172a', 'background' => '#ffffff', 'radius' => 12, 'fontWeight' => 800, 'align' => 'center'],
                'link' => '/',
                'target' => '_self',
            ],
        ];
    }
}
