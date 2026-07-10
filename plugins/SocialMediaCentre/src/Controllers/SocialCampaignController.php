<?php

namespace Plugins\SocialMediaCentre\src\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Plugins\SocialMediaCentre\src\Models\SocialCampaign;

class SocialCampaignController extends Controller
{
    public function index()
    {
        $campaigns = SocialCampaign::withCount('posts')->latest()->paginate(15);
        return view('plugins.SocialMediaCentre::admin.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('plugins.SocialMediaCentre::admin.campaigns.form', ['campaign' => new SocialCampaign(['status' => 'active'])]);
    }

    public function show(SocialCampaign $campaign)
    {
        $campaign->load(['posts' => fn ($query) => $query->with(['media', 'publications'])->latest()]);
        $posts = $campaign->posts;

        $stats = [
            'posts' => $posts->count(),
            'scheduled' => $posts->where('status', 'scheduled')->count(),
            'published' => $posts->where('status', 'published')->count(),
            'ready' => $posts->where('status', 'ready')->count(),
            'failed' => $posts->where('status', 'failed')->count(),
        ];

        return view('plugins.SocialMediaCentre::admin.campaigns.show', compact('campaign', 'posts', 'stats'));
    }

    public function store(Request $request)
    {
        SocialCampaign::create($this->data($request));
        return redirect()->route('admin.social.campaigns.index')->with('success', 'Campaign created.');
    }

    public function edit(SocialCampaign $campaign)
    {
        return view('plugins.SocialMediaCentre::admin.campaigns.form', compact('campaign'));
    }

    public function update(Request $request, SocialCampaign $campaign)
    {
        $campaign->update($this->data($request, $campaign));
        return redirect()->route('admin.social.campaigns.index')->with('success', 'Campaign updated.');
    }

    public function destroy(SocialCampaign $campaign)
    {
        $campaign->delete();
        return back()->with('success', 'Campaign deleted.');
    }

    protected function data(Request $request, ?SocialCampaign $campaign = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|string|max:255',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'status' => 'required|in:active,paused,completed',
            'notes' => 'nullable|string|max:5000',
        ]);

        $slug = Str::slug($validated['name']);
        $base = $slug;
        $i = 2;
        while (SocialCampaign::where('slug', $slug)->when($campaign, fn ($q) => $q->whereKeyNot($campaign->id))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $validated + ['slug' => $slug];
    }
}
