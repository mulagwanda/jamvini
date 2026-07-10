<?php

namespace Plugins\SEO\src\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\SEO\src\Models\SeoAnalyticsEvent;

class SeoAnalyticsService
{
    public function record(Request $request): void
    {
        if (!Schema::hasTable('seo_analytics_events')) {
            return;
        }

        $url = (string) $request->input('url', $request->headers->get('referer', url('/')));
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $visitor = (string) $request->input('visitor_id', Str::uuid());

        SeoAnalyticsEvent::create([
            'visitor_id' => $visitor,
            'session_id' => substr((string) $request->input('session_id', session()->getId()), 0, 120),
            'event_type' => (string) $request->input('event_type', 'pageview'),
            'url' => substr($url, 0, 2048),
            'path' => substr($path, 0, 1024),
            'path_hash' => hash('sha256', $path),
            'title' => substr((string) $request->input('title', ''), 0, 255) ?: null,
            'referrer' => substr((string) $request->input('referrer', ''), 0, 2048) ?: null,
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'device_type' => $this->deviceType($request->userAgent() ?? ''),
            'browser' => $this->browser($request->userAgent() ?? ''),
            'ip_hash' => hash('sha256', $request->ip() . config('app.key')),
            'metadata' => [
                'screen' => $request->input('screen'),
                'language' => $request->input('language'),
            ],
            'occurred_at' => now(),
        ]);
    }

    public function report(int $days = 30): array
    {
        if (!Schema::hasTable('seo_analytics_events')) {
            return $this->emptyReport();
        }

        $since = now()->subDays($days);
        $base = SeoAnalyticsEvent::query()->where('occurred_at', '>=', $since);

        return [
            'pageviews' => (clone $base)->where('event_type', 'pageview')->count(),
            'visitors' => (clone $base)->distinct('visitor_id')->count('visitor_id'),
            'online' => SeoAnalyticsEvent::query()->where('occurred_at', '>=', now()->subMinutes(5))->distinct('visitor_id')->count('visitor_id'),
            'top_pages' => (clone $base)->select('path', DB::raw('count(*) as views'))->where('event_type', 'pageview')->groupBy('path')->orderByDesc('views')->limit(8)->get(),
            'sources' => (clone $base)->select(DB::raw("coalesce(utm_source, 'direct') as source"), DB::raw('count(*) as visits'))->groupBy('source')->orderByDesc('visits')->limit(8)->get(),
            'devices' => (clone $base)->select('device_type', DB::raw('count(*) as visits'))->groupBy('device_type')->orderByDesc('visits')->get(),
            'recent' => SeoAnalyticsEvent::query()->latest('occurred_at')->limit(12)->get(),
        ];
    }

    protected function emptyReport(): array
    {
        return ['pageviews' => 0, 'visitors' => 0, 'online' => 0, 'top_pages' => collect(), 'sources' => collect(), 'devices' => collect(), 'recent' => collect()];
    }

    protected function deviceType(string $agent): string
    {
        return preg_match('/mobile|android|iphone|ipod/i', $agent) ? 'mobile' : (preg_match('/ipad|tablet/i', $agent) ? 'tablet' : 'desktop');
    }

    protected function browser(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Firefox') => 'Firefox',
            str_contains($agent, 'Edg') => 'Edge',
            str_contains($agent, 'Chrome') => 'Chrome',
            str_contains($agent, 'Safari') => 'Safari',
            default => 'Other',
        };
    }
}
