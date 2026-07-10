<?php

namespace Plugins\SocialMediaCentre\src\Services;

use Illuminate\Support\Facades\Schema;
use Plugins\SocialMediaCentre\src\Models\SocialAccount;
use Plugins\SocialMediaCentre\src\Models\SocialPost;
use Plugins\SocialMediaCentre\src\Models\SocialPostPublication;

class SocialPublishingService
{
    public function syncPublications(SocialPost $post): void
    {
        if (!Schema::hasTable('social_post_publications')) {
            return;
        }

        $platforms = $post->platforms ?: [];
        $existing = $post->publications()->get()->keyBy('platform');

        foreach ($platforms as $platform) {
            $account = SocialAccount::where('platform', $platform)
                ->whereIn('status', ['connected', 'manual'])
                ->orderByRaw("status = 'connected' desc")
                ->first();

            SocialPostPublication::updateOrCreate(
                ['post_id' => $post->id, 'platform' => $platform],
                [
                    'account_id' => $account?->id,
                    'mode' => $account?->status === 'connected' ? 'api' : 'manual',
                    'status' => $existing[$platform]->status ?? $this->initialStatus($post),
                    'scheduled_at' => $post->scheduled_at,
                ]
            );
        }

        $post->publications()
            ->whereNotIn('platform', $platforms ?: ['__none__'])
            ->whereIn('status', ['pending', 'queued', 'manual_required'])
            ->update(['status' => 'skipped', 'notes' => 'Platform removed from post.']);
    }

    public function markPublished(SocialPostPublication $publication, ?string $url = null, ?string $notes = null): void
    {
        $publication->update([
            'status' => 'published',
            'published_at' => now(),
            'provider_url' => $url ?: $publication->provider_url,
            'notes' => $notes ?: $publication->notes,
            'last_error' => null,
        ]);

        $this->refreshPostStatus($publication->post);
    }

    public function markFailed(SocialPostPublication $publication, string $error): void
    {
        $publication->update([
            'status' => 'failed',
            'last_error' => $error,
            'attempts' => $publication->attempts + 1,
        ]);

        $publication->post->logs()->create([
            'platform' => $publication->platform,
            'status' => 'error',
            'message' => 'Publishing failed: ' . $error,
        ]);

        $this->refreshPostStatus($publication->post);
    }

    public function runDueQueue(int $limit = 25): int
    {
        if (!Schema::hasTable('social_post_publications')) {
            return 0;
        }

        $due = SocialPostPublication::with(['post', 'account'])
            ->whereIn('status', ['pending', 'queued', 'failed'])
            ->where(function ($query) {
                $query->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            })
            ->limit($limit)
            ->get();

        foreach ($due as $publication) {
            $this->attempt($publication);
        }

        return $due->count();
    }

    protected function attempt(SocialPostPublication $publication): void
    {
        $publication->update([
            'status' => 'queued',
            'queued_at' => now(),
            'attempts' => $publication->attempts + 1,
            'request_payload' => [
                'post_id' => $publication->post_id,
                'platform' => $publication->platform,
                'title' => $publication->post->title,
                'caption' => $publication->post->caption,
                'link_url' => $publication->post->link_url,
            ],
        ]);

        if ($publication->mode !== 'api') {
            $publication->update([
                'status' => 'manual_required',
                'last_error' => 'No connected API account. Manual publishing is required.',
            ]);
            return;
        }

        $publication->update([
            'status' => 'manual_required',
            'last_error' => 'API publisher for ' . $publication->platform . ' is not enabled yet.',
        ]);
    }

    protected function initialStatus(SocialPost $post): string
    {
        return in_array($post->status, ['scheduled', 'ready'], true) ? 'pending' : 'draft';
    }

    protected function refreshPostStatus(SocialPost $post): void
    {
        $post->load('publications');
        if ($post->publications->isNotEmpty() && $post->publications->every(fn ($publication) => $publication->status === 'published')) {
            $post->update([
                'status' => 'published',
                'published_at' => $post->published_at ?: now(),
            ]);
        } elseif ($post->publications->contains(fn ($publication) => $publication->status === 'failed')) {
            $post->update(['status' => 'failed']);
        }
    }
}
