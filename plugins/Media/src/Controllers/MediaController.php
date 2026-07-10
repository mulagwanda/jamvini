<?php

namespace Plugins\Media\src\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Hooks\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Plugins\Media\src\Models\Media;

class MediaController extends Controller
{
    protected $imageManager;

    public function __construct()
    {
        if (class_exists(ImageManager::class)) {
            $this->imageManager = new ImageManager(new Driver());
        }
    }

    public function index(Request $request)
    {
        $folder = $request->get('folder', 'general');
        $search = trim($request->string('search')->toString());
        $media = Media::where('folder', $folder)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('original_name', 'like', "%{$search}%")
                        ->orWhere('filename', 'like', "%{$search}%")
                        ->orWhere('alt_text', 'like', "%{$search}%")
                        ->orWhere('mime_type', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(24);
        $folders = Media::getFolders();
        $unsplashResults = collect();
        $unsplashMeta = ['page' => 1, 'total_pages' => 0, 'has_more' => false];

        if ($request->filled('unsplash_query')) {
            $unsplashPayload = $this->unsplashSearchPayload($request->string('unsplash_query')->toString());
            $unsplashResults = collect($unsplashPayload['results']);
            $unsplashMeta = $unsplashPayload['meta'];
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $media->count(),
                'html' => $media->getCollection()
                    ->map(fn ($file) => view('plugins.Media::admin.media.partials.card', compact('file'))->render())
                    ->implode(''),
            ]);
        }

        return view('plugins.Media::admin.media.index', compact('media', 'folders', 'folder', 'unsplashResults', 'unsplashMeta', 'search'));
    }

    public function create()
    {
        $folders = Media::getFolders();
        return view('plugins.Media::admin.media.create', compact('folders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required',
            'files.*' => 'file|max:10240',
            'folder' => 'nullable|string|max:50',
            'new_folder' => 'nullable|string|max:50',
        ]);

        $folder = $request->input('folder', 'general');
        if ($folder === '__new__') {
            $folder = $request->input('new_folder', 'general');
        }
        $folder = str($folder ?: 'general')->slug('-')->toString();
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store("media/{$folder}", 'public');
            
            $media = Media::create([
                'filename' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $path,
                'folder' => $folder,
                'source' => 'upload',
                'uploaded_by' => auth('admin')->id(),
            ]);

            // Generate thumbnail for images
            if ($media->is_image && isset($this->imageManager)) {
                try {
                    $thumbPath = "media/{$folder}/thumb_" . basename($path);
                    $image = $this->imageManager->read(Storage::disk('public')->path($path));
                    $image->scale(width: 300);
                    Storage::disk('public')->put($thumbPath, $image->toJpeg());
                    $media->update(['thumbnail_path' => $thumbPath]);
                } catch (\Exception $e) {
                    // Skip thumbnail if fails
                }
            }

            $uploaded[] = $media;
            Action::do('media.uploaded', $media);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' file(s) uploaded.',
                'folder' => $folder,
                'files' => collect($uploaded)->map(fn($m) => [
                    'id' => $m->id,
                    'url' => $m->url,
                    'thumbnail_url' => $m->thumbnail_url,
                    'name' => $m->original_name,
                    'html' => view('plugins.Media::admin.media.partials.card', ['file' => $m])->render(),
                ]),
            ]);
        }

        return redirect()->route('admin.media.index', ['folder' => $folder])
            ->with('success', count($uploaded) . ' file(s) uploaded!');
    }

    public function edit(Media $medium)
    {
        return view('plugins.Media::admin.media.edit', compact('medium'));
    }

    public function update(Request $request, Media $medium)
    {
        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'folder' => 'nullable|string|max:50',
        ]);
        $validated['folder'] = str($validated['folder'] ?: 'general')->slug('-')->toString();

        // Move file if folder changed
        if ($validated['folder'] && $validated['folder'] !== $medium->folder) {
            $newPath = "media/{$validated['folder']}/" . $medium->filename;
            if (Storage::disk('public')->exists($medium->path)) {
                Storage::disk('public')->move($medium->path, $newPath);
            }
            if ($medium->thumbnail_path && Storage::disk('public')->exists($medium->thumbnail_path)) {
                $newThumb = "media/{$validated['folder']}/thumb_" . $medium->filename;
                Storage::disk('public')->move($medium->thumbnail_path, $newThumb);
                $validated['thumbnail_path'] = $newThumb;
            }
            $validated['path'] = $newPath;
        }

        $medium->update($validated);

        return redirect()->route('admin.media.index')
            ->with('success', 'File updated!');
    }

    public function destroy(Request $request, Media $medium)
    {
        $id = $medium->id;
        $medium->deleteWithFile();
        Action::do('media.deleted', $medium);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id' => $id,
                'message' => 'File deleted.',
            ]);
        }

        return back()->with('success', 'File deleted!');
    }

    public function searchUnsplash(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:120',
            'page' => 'nullable|integer|min:1|max:100',
        ]);

        $payload = $this->unsplashSearchPayload($validated['query'], (int) ($validated['page'] ?? 1));
        $results = $payload['results'];

        return response()->json([
            'results' => $results,
            'meta' => $payload['meta'],
            'html' => collect($results)
                ->map(fn ($photo) => view('plugins.Media::admin.media.partials.unsplash-card', compact('photo'))->render())
                ->implode(''),
        ]);
    }

    public function importUnsplash(Request $request)
    {
        $validated = $request->validate([
            'photo_id' => 'required|string|max:120',
            'image_url' => 'required|url|max:2000',
            'download_location' => 'nullable|url|max:2000',
            'description' => 'nullable|string|max:255',
            'photographer_name' => 'nullable|string|max:255',
            'photographer_url' => 'nullable|url|max:1000',
            'folder' => 'nullable|string|max:50',
        ]);

        if (!empty($validated['download_location'])) {
            $this->unsplashClient()->get($validated['download_location']);
        }

        $media = $this->importRemoteImage($validated['image_url'], [
            'folder' => $validated['folder'] ?? 'unsplash',
            'source' => 'unsplash',
            'external_id' => $validated['photo_id'],
            'original_name' => Str::slug($validated['description'] ?: 'unsplash-' . $validated['photo_id']) . '.jpg',
            'alt_text' => $validated['description'] ?? null,
            'attribution' => [
                'provider' => 'Unsplash',
                'photographer_name' => $validated['photographer_name'] ?? null,
                'photographer_url' => $validated['photographer_url'] ?? null,
            ],
            'metadata' => [
                'download_location' => $validated['download_location'] ?? null,
            ],
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Unsplash image imported.',
                'folder' => $media->folder,
                'file' => [
                    'id' => $media->id,
                    'html' => view('plugins.Media::admin.media.partials.card', ['file' => $media])->render(),
                ],
            ]);
        }

        return redirect()->route('admin.media.index', ['folder' => $media->folder])
            ->with('success', 'Unsplash image imported into Media Library.');
    }

    public function generateImage(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000',
            'folder' => 'nullable|string|max:50',
        ]);

        $token = (string) env('REPLICATE_API_TOKEN', '');
        if ($token === '') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'REPLICATE_API_TOKEN is not configured.'], 422);
            }
            return back()->with('error', 'REPLICATE_API_TOKEN is not configured.');
        }

        $model = trim((string) env('REPLICATE_IMAGE_MODEL', 'black-forest-labs/flux-1.1-pro'), '/');
        $response = Http::withToken($token)
            ->withHeaders(['Prefer' => 'wait'])
            ->timeout(90)
            ->post("https://api.replicate.com/v1/models/{$model}/predictions", [
                'input' => [
                    'prompt' => $validated['prompt'],
                ],
            ]);

        if (!$response->successful()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Image generation failed: ' . Str::limit($response->body(), 220)], 422);
            }
            return back()->with('error', 'Image generation failed: ' . Str::limit($response->body(), 220));
        }

        $data = $response->json();
        $output = data_get($data, 'output');
        $imageUrl = is_array($output) ? collect($output)->first(fn ($value) => is_string($value)) : $output;

        if (!is_string($imageUrl) || !str_starts_with($imageUrl, 'http')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Image generation did not return an image URL yet. Try again in a moment or use a faster model.'], 422);
            }
            return back()->with('error', 'Image generation did not return an image URL yet. Try again in a moment or use a faster model.');
        }

        $media = $this->importRemoteImage($imageUrl, [
            'folder' => $validated['folder'] ?? 'ai-generated',
            'source' => 'ai',
            'external_id' => data_get($data, 'id'),
            'original_name' => Str::slug(Str::limit($validated['prompt'], 60, '')) . '.png',
            'alt_text' => $validated['prompt'],
            'metadata' => [
                'provider' => 'Replicate',
                'model' => $model,
                'prompt' => $validated['prompt'],
                'prediction_id' => data_get($data, 'id'),
                'prediction_url' => data_get($data, 'urls.web'),
            ],
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'AI image generated and saved.',
                'folder' => $media->folder,
                'file' => [
                    'id' => $media->id,
                    'html' => view('plugins.Media::admin.media.partials.card', ['file' => $media])->render(),
                ],
            ]);
        }

        return redirect()->route('admin.media.index', ['folder' => $media->folder])
            ->with('success', 'AI image generated and saved into Media Library.');
    }

    // AJAX endpoint for media picker modal
    public function picker(Request $request)
    {
        $type = $request->get('type', 'all');
        $media = Media::query()
            ->when($request->filled('folder'), fn ($q) => $q->where('folder', $request->get('folder')))
            ->when($request->search, fn($q, $s) => $q->where('original_name', 'like', "%{$s}%"))
            ->when($type === 'image', fn ($q) => $q->where('mime_type', 'like', 'image/%'))
            ->when($type === 'video', fn ($q) => $q->where('mime_type', 'like', 'video/%'))
            ->when($type === 'visual', fn ($q) => $q->where(function ($inner) {
                $inner->where('mime_type', 'like', 'image/%')
                    ->orWhere('mime_type', 'like', 'video/%');
            }))
            ->latest()
            ->paginate(20);
        $folders = Media::getFolders();

        if ($request->wantsJson()) {
            return response()->json([
                'items' => $media->getCollection()->map(fn ($file) => [
                    'id' => $file->id,
                    'name' => $file->original_name,
                    'url' => $file->url,
                    'thumbnail_url' => $file->thumbnail_url,
                    'mime_type' => $file->mime_type,
                    'is_image' => $file->is_image,
                    'is_video' => str_starts_with($file->mime_type, 'video/'),
                    'folder' => $file->folder,
                    'size' => $file->size_for_humans,
                ]),
                'pagination' => [
                    'current_page' => $media->currentPage(),
                    'last_page' => $media->lastPage(),
                    'has_more' => $media->hasMorePages(),
                ],
            ]);
        }

        if ($request->ajax()) {
            return view('plugins.Media::admin.media.picker-grid', compact('media', 'folders'))->render();
        }

        return view('plugins.Media::admin.media.picker', compact('media', 'folders'));
    }

    protected function unsplashResults(string $query): array
    {
        return $this->unsplashSearchPayload($query)['results'];
    }

    protected function unsplashSearchPayload(string $query, int $page = 1): array
    {
        $page = max(1, min($page, 100));
        $empty = [
            'results' => [],
            'meta' => [
                'page' => $page,
                'total_pages' => 0,
                'has_more' => false,
            ],
        ];

        if ((string) env('UNSPLASH_ACCESS_KEY', '') === '') {
            return $empty;
        }

        $response = $this->unsplashClient()->get('https://api.unsplash.com/search/photos', [
            'query' => $query,
            'page' => $page,
            'per_page' => 12,
            'orientation' => 'landscape',
            'content_filter' => 'high',
        ]);

        if (!$response->successful()) {
            return $empty;
        }

        $totalPages = (int) $response->json('total_pages', 0);

        return [
            'results' => collect($response->json('results', []))->map(fn ($photo) => [
                'id' => data_get($photo, 'id'),
                'description' => data_get($photo, 'alt_description') ?: data_get($photo, 'description') ?: 'Unsplash image',
                'thumb' => data_get($photo, 'urls.small'),
                'regular' => data_get($photo, 'urls.regular'),
                'download_location' => data_get($photo, 'links.download_location'),
                'photographer_name' => data_get($photo, 'user.name'),
                'photographer_url' => data_get($photo, 'user.links.html'),
            ])->filter(fn ($photo) => $photo['id'] && $photo['regular'])->values()->all(),
            'meta' => [
                'page' => $page,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages,
            ],
        ];
    }

    protected function unsplashClient()
    {
        return Http::withHeaders([
            'Authorization' => 'Client-ID ' . env('UNSPLASH_ACCESS_KEY'),
            'Accept-Version' => 'v1',
        ])->timeout(20);
    }

    protected function importRemoteImage(string $url, array $attributes): Media
    {
        $folder = str($attributes['folder'] ?? 'imports')->slug('-')->toString();
        $response = Http::timeout(60)->get($url);

        if (!$response->successful()) {
            abort(422, 'Could not download the remote image.');
        }

        $contentType = $response->header('Content-Type', 'image/jpeg');
        $extension = match (true) {
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            default => 'jpg',
        };

        $baseName = pathinfo($attributes['original_name'] ?? 'media-import.' . $extension, PATHINFO_FILENAME);
        $filename = Str::slug($baseName ?: 'media-import') . '-' . Str::random(8) . '.' . $extension;
        $path = "media/{$folder}/{$filename}";
        Storage::disk('public')->put($path, $response->body());

        $media = Media::create([
            'filename' => $filename,
            'original_name' => $attributes['original_name'] ?? $filename,
            'mime_type' => $contentType,
            'size' => strlen($response->body()),
            'path' => $path,
            'folder' => $folder,
            'source' => $attributes['source'] ?? 'import',
            'external_id' => $attributes['external_id'] ?? null,
            'attribution' => $attributes['attribution'] ?? null,
            'metadata' => $attributes['metadata'] ?? null,
            'alt_text' => $attributes['alt_text'] ?? null,
            'uploaded_by' => auth('admin')->id(),
        ]);

        if ($media->is_image && isset($this->imageManager)) {
            try {
                $thumbPath = "media/{$folder}/thumb_{$filename}";
                $image = $this->imageManager->read(Storage::disk('public')->path($path));
                $image->scale(width: 300);
                Storage::disk('public')->put($thumbPath, $image->toJpeg());
                $media->update(['thumbnail_path' => $thumbPath]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        Action::do('media.uploaded', $media);

        return $media;
    }
}
