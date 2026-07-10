<?php

namespace Plugins\SocialMediaCentre\src\Controllers;

use App\Core\ActivityLogger;
use App\Core\Hooks\Action;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Plugins\Media\src\Models\Media;
use Plugins\SocialMediaCentre\src\Models\SocialAccount;
use Plugins\SocialMediaCentre\src\Models\SocialCampaign;
use Plugins\SocialMediaCentre\src\Models\SocialPost;
use Plugins\SocialMediaCentre\src\Models\SocialPostPublication;
use Plugins\SocialMediaCentre\src\Models\SocialPostTemplate;
use Plugins\SocialMediaCentre\src\Services\SocialAiAssistantService;
use Plugins\SocialMediaCentre\src\Services\SocialPublishingService;

class SocialPostController extends Controller
{
    protected array $platforms = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'x' => 'X',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
    ];

    public function dashboard()
    {
        $hasPublications = Schema::hasTable('social_post_publications');
        $stats = [
            'drafts' => SocialPost::where('status', 'draft')->count(),
            'scheduled' => SocialPost::where('status', 'scheduled')->count(),
            'published' => SocialPost::where('status', 'published')->count(),
            'campaigns' => SocialCampaign::where('status', 'active')->count(),
            'manual_required' => $hasPublications ? SocialPostPublication::where('status', 'manual_required')->count() : 0,
            'failed_publications' => $hasPublications ? SocialPostPublication::where('status', 'failed')->count() : 0,
        ];

        $upcoming = SocialPost::with('campaign')->where('status', 'scheduled')->orderBy('scheduled_at')->limit(8)->get();
        $recent = SocialPost::with('campaign')->latest()->limit(8)->get();

        return view('plugins.SocialMediaCentre::admin.index', compact('stats', 'upcoming', 'recent'));
    }

    public function calendar(Request $request)
    {
        $month = Carbon::parse($request->get('month', now()->format('Y-m')) . '-01')->startOfMonth();
        $start = $month->copy()->startOfWeek();
        $end = $month->copy()->endOfMonth()->endOfWeek();

        $posts = SocialPost::with(['campaign', 'media'])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('scheduled_at', [$start, $end])
                    ->orWhereBetween('published_at', [$start, $end]);
            })
            ->orderByRaw('coalesce(scheduled_at, published_at, created_at) asc')
            ->get();

        $readyPosts = SocialPost::with(['campaign', 'media'])
            ->whereIn('status', ['ready', 'draft', 'failed'])
            ->whereNull('scheduled_at')
            ->latest()
            ->limit(10)
            ->get();

        $postsByDate = $posts->groupBy(function (SocialPost $post) {
            return ($post->scheduled_at ?: $post->published_at ?: $post->created_at)->format('Y-m-d');
        });

        $days = [];
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $key = $date->format('Y-m-d');
            $days[] = [
                'date' => $date->copy(),
                'key' => $key,
                'in_month' => $date->month === $month->month,
                'posts' => $postsByDate->get($key, collect()),
            ];
        }

        $stats = [
            'scheduled' => $posts->where('status', 'scheduled')->count(),
            'published' => $posts->where('status', 'published')->count(),
            'ready' => $readyPosts->where('status', 'ready')->count(),
            'failed' => $posts->where('status', 'failed')->count() + $readyPosts->where('status', 'failed')->count(),
        ];

        return view('plugins.SocialMediaCentre::admin.calendar', [
            'month' => $month,
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'currentMonth' => now()->format('Y-m'),
            'days' => $days,
            'readyPosts' => $readyPosts,
            'stats' => $stats,
            'platforms' => $this->platforms,
        ]);
    }

    public function index(Request $request)
    {
        $posts = SocialPost::with(['campaign', 'media'])
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->campaign_id, fn ($query, $campaignId) => $query->where('campaign_id', $campaignId))
            ->latest()
            ->paginate(15);

        $campaigns = SocialCampaign::orderBy('name')->get();

        return view('plugins.SocialMediaCentre::admin.posts.index', compact('posts', 'campaigns'));
    }

    public function create(Request $request)
    {
        $template = $request->filled('template')
            ? SocialPostTemplate::where('status', 'active')->find($request->integer('template'))
            : null;

        $defaultPlatforms = array_filter(explode(',', Setting::get('social_default_platforms', 'facebook,instagram,linkedin')));
        $post = new SocialPost([
            'campaign_id' => $request->integer('campaign_id') ?: null,
            'status' => Setting::get('social_default_status', 'draft'),
            'hashtags' => $this->normalizeHashtags(Setting::get('social_default_hashtags', '')),
            'platforms' => array_values(array_intersect($defaultPlatforms, array_keys($this->platforms))),
        ]);
        if ($template) {
            $values = $request->input('values', []);
            $post->title = $this->templatePreview($template->title_template, $values);
            $post->caption = $this->templatePreview($template->caption_template, $values);
            $post->hashtags = $template->hashtags ?? [];
            $post->platforms = $template->platforms ?? [];
            $post->notes = 'Created from template: ' . $template->name;
        }

        return view('plugins.SocialMediaCentre::admin.posts.form', $this->formData($post) + ['selectedTemplate' => $template]);
    }

    public function store(Request $request, SocialPublishingService $publishing)
    {
        $data = $this->postData($request);
        $data['created_by'] = auth('admin')->id();

        $post = SocialPost::create($data);
        $this->syncMedia($post, $request->input('media_ids', []));
        $publishing->syncPublications($post);
        $post->logs()->create(['status' => 'info', 'message' => 'Post created in JamVini Social Media Centre.']);

        Action::do('social.post_created', $post);
        if ($post->status === 'scheduled') {
            Action::do('social.post_scheduled', $post);
        }

        return redirect()->route('admin.social.posts.show', $post)->with('success', 'Social post created.');
    }

    public function show(SocialPost $post)
    {
        $relations = ['campaign', 'media', 'logs'];
        if (Schema::hasTable('social_post_publications')) {
            $relations[] = 'publications.account';
        }
        $post->load($relations);
        return view('plugins.SocialMediaCentre::admin.posts.show', compact('post') + ['platforms' => $this->platforms]);
    }

    public function edit(SocialPost $post)
    {
        return view('plugins.SocialMediaCentre::admin.posts.form', $this->formData($post->load('media')));
    }

    public function update(Request $request, SocialPost $post, SocialPublishingService $publishing)
    {
        $post->update($this->postData($request));
        $this->syncMedia($post, $request->input('media_ids', []));
        $publishing->syncPublications($post);
        $post->logs()->create(['status' => 'info', 'message' => 'Post updated.']);

        return redirect()->route('admin.social.posts.show', $post)->with('success', 'Social post updated.');
    }

    public function destroy(SocialPost $post)
    {
        $post->delete();
        return redirect()->route('admin.social.posts.index')->with('success', 'Social post deleted.');
    }

    public function markPublished(SocialPost $post, SocialPublishingService $publishing)
    {
        $post->loadMissing('publications');
        foreach ($post->publications as $publication) {
            if ($publication->status !== 'published') {
                $publishing->markPublished($publication, null, 'Marked published with parent post.');
            }
        }

        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $post->logs()->create(['status' => 'success', 'message' => 'Post marked as manually published.']);

        ActivityLogger::log('social.post.published', 'SocialPost', $post->id, 'Social post marked as published.');
        Action::do('social.post_published', $post);

        return back()->with('success', 'Post marked as published.');
    }

    public function duplicate(SocialPost $post, SocialPublishingService $publishing)
    {
        $post->loadMissing('media');
        $copy = $post->replicate(['status', 'published_at']);
        $copy->title = $post->title . ' Copy';
        $copy->status = 'draft';
        $copy->scheduled_at = null;
        $copy->published_at = null;
        $copy->created_by = auth('admin')->id();
        $copy->save();
        $this->syncMedia($copy, $post->media->pluck('id')->all());
        $publishing->syncPublications($copy);
        $copy->logs()->create(['status' => 'info', 'message' => 'Post duplicated.']);

        return redirect()->route('admin.social.posts.edit', $copy)->with('success', 'Post duplicated.');
    }

    public function markPublicationPublished(Request $request, SocialPostPublication $publication, SocialPublishingService $publishing)
    {
        $validated = $request->validate([
            'provider_url' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $publishing->markPublished($publication, $validated['provider_url'] ?? null, $validated['notes'] ?? null);
        $publication->post->logs()->create([
            'platform' => $publication->platform,
            'status' => 'success',
            'message' => ucfirst($publication->platform) . ' marked as published.',
        ]);

        return back()->with('success', 'Platform marked as published.');
    }

    public function markPublicationFailed(Request $request, SocialPostPublication $publication, SocialPublishingService $publishing)
    {
        $validated = $request->validate([
            'last_error' => 'required|string|max:1000',
        ]);

        $publishing->markFailed($publication, $validated['last_error']);

        return back()->with('success', 'Platform marked as failed.');
    }

    public function runPublishingQueue(SocialPublishingService $publishing)
    {
        $count = $publishing->runDueQueue();

        return back()->with('success', "Publishing queue checked. {$count} item(s) processed.");
    }

    public function syncPublications(SocialPost $post, SocialPublishingService $publishing)
    {
        $publishing->syncPublications($post);

        return back()->with('success', 'Publishing records synced.');
    }

    public function aiSuggest(Request $request, SocialAiAssistantService $assistant)
    {
        $validated = $request->validate([
            'action' => 'required|in:generate_caption,improve_caption,shorten_for_x,generate_hashtags,image_prompt',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:5000',
            'hashtags' => 'nullable|string|max:1000',
            'brief' => 'nullable|string|max:2000',
            'tone' => 'nullable|string|max:120',
            'platforms' => 'nullable|array',
            'platforms.*' => 'string|max:40',
        ]);

        $result = $assistant->generate($validated['action'], [
            'title' => $validated['title'] ?? '',
            'caption' => $validated['caption'] ?? '',
            'hashtags' => $validated['hashtags'] ?? '',
            'brief' => $validated['brief'] ?? '',
            'tone' => $validated['tone'] ?? '',
            'platforms' => array_values(array_intersect($validated['platforms'] ?? [], array_keys($this->platforms))),
        ]);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    protected function formData(SocialPost $post): array
    {
        $selectedIds = $post->exists ? $post->media()->pluck('cms_media.id')->all() : [];
        $media = Media::query()
            ->where(function ($query) use ($selectedIds) {
                $query->where('mime_type', 'like', 'image/%')
                    ->orWhere('mime_type', 'like', 'video/%')
                    ->when($selectedIds, fn ($inner) => $inner->orWhereIn('id', $selectedIds));
            })
            ->latest()
            ->limit(60)
            ->get()
            ->unique('id')
            ->values();

        return [
            'post' => $post,
            'campaigns' => SocialCampaign::where('status', 'active')->orderBy('name')->get(),
            'media' => $media,
            'platforms' => $this->platforms,
            'accountNames' => SocialAccount::query()
                ->whereIn('platform', array_keys($this->platforms))
                ->orderByRaw("status = 'connected' desc")
                ->orderBy('name')
                ->get()
                ->groupBy('platform')
                ->map(fn ($accounts) => $accounts->first()->name)
                ->all(),
            'socialSettings' => [
                'ai_tone' => Setting::get('social_ai_tone', 'professional, friendly, clear'),
                'ai_default_brief' => Setting::get('social_ai_default_brief', ''),
                'brand_voice' => Setting::get('social_brand_voice', ''),
                'timezone' => Setting::get('social_timezone', Setting::get('timezone', config('app.timezone'))),
                'approval_required' => Setting::get('social_approval_required', '0'),
            ],
        ];
    }

    protected function postData(Request $request): array
    {
        $validated = $request->validate([
            'campaign_id' => 'nullable|exists:social_campaigns,id',
            'title' => 'required|string|max:255',
            'caption' => 'required|string|max:5000',
            'link_url' => 'nullable|url|max:500',
            'hashtags' => 'nullable|string|max:1000',
            'platforms' => 'nullable|array',
            'platforms.*' => 'string|max:40',
            'status' => 'required|in:draft,scheduled,ready,published,failed',
            'scheduled_at' => 'required_if:status,scheduled|nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);

        $platforms = array_values(array_intersect($validated['platforms'] ?? [], array_keys($this->platforms)));
        $hashtags = $this->normalizeHashtags($validated['hashtags'] ?? '');

        return [
            'campaign_id' => $validated['campaign_id'] ?? null,
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'link_url' => $this->trackedUrl($validated['link_url'] ?? null, $validated['title']),
            'hashtags' => $hashtags,
            'platforms' => $platforms,
            'status' => $validated['status'],
            'scheduled_at' => $validated['status'] === 'scheduled' ? ($validated['scheduled_at'] ?? null) : null,
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    protected function normalizeHashtags(string $value): array
    {
        return collect(preg_split('/[\s,]+/', $value))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->map(fn ($tag) => str_starts_with($tag, '#') ? $tag : '#' . $tag)
            ->unique()
            ->values()
            ->all();
    }

    protected function trackedUrl(?string $url, string $title): ?string
    {
        if (!$url || Setting::get('social_utm_enabled', '0') !== '1') {
            return $url;
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return $url;
        }

        parse_str($parts['query'] ?? '', $query);
        $query = array_filter($query + [
            'utm_source' => Setting::get('social_utm_source', 'social'),
            'utm_medium' => Setting::get('social_utm_medium', 'organic'),
            'utm_campaign' => trim(Setting::get('social_utm_campaign_prefix', 'jamvini') . '-' . str($title)->slug('-')->toString(), '-'),
        ]);

        $rebuilt = $parts['scheme'] . '://' . $parts['host'] . ($parts['path'] ?? '');
        $rebuilt .= '?' . http_build_query($query);
        $rebuilt .= isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $rebuilt;
    }

    protected function syncMedia(SocialPost $post, array $ids): void
    {
        $sync = [];
        foreach (array_values(array_filter($ids)) as $index => $id) {
            $sync[$id] = ['sort_order' => $index];
        }
        $post->media()->sync($sync);
    }

    protected function templatePreview(string $value, array $variables = []): string
    {
        return preg_replace_callback('/{{\s*([a-zA-Z0-9_]+)\s*}}/', function ($matches) use ($variables) {
            $key = $matches[1];

            return filled($variables[$key] ?? null) ? $variables[$key] : '[' . str_replace('_', ' ', $key) . ']';
        }, $value);
    }
}
