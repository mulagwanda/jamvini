<?php

namespace Plugins\SEO\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\SEO\src\Models\SeoMeta;
use Plugins\SEO\src\Services\SeoAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeoController extends Controller
{
    public function index(SeoAnalyticsService $analytics)
    {
        $settings = [
            'site_title' => SeoMeta::setting('site_title', config('app.name')),
            'site_description' => SeoMeta::setting('site_description', ''),
            'google_analytics' => SeoMeta::setting('google_analytics', ''),
            'facebook_pixel' => SeoMeta::setting('facebook_pixel', ''),
            'google_verification' => SeoMeta::setting('google_verification', ''),
            'sitemap_enabled' => SeoMeta::setting('sitemap_enabled', '1'),
            'robots_policy' => SeoMeta::setting('robots_policy', 'allow'),
            'analytics_enabled' => SeoMeta::setting('analytics_enabled', '1'),
            'schema_org_enabled' => SeoMeta::setting('schema_org_enabled', '1'),
            'organization_name' => SeoMeta::setting('organization_name', config('app.name')),
            'organization_logo' => SeoMeta::setting('organization_logo', ''),
        ];
        $report = $analytics->report(30);
        $contentAudit = $this->contentAudit();

        return view('plugins.SEO::admin.index', compact('settings', 'report', 'contentAudit'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_title' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:320',
            'google_analytics' => 'nullable|string',
            'facebook_pixel' => 'nullable|string',
            'google_verification' => 'nullable|string',
            'sitemap_enabled' => 'boolean',
            'robots_policy' => 'nullable|in:allow,disallow',
            'analytics_enabled' => 'boolean',
            'schema_org_enabled' => 'boolean',
            'organization_name' => 'nullable|string|max:255',
            'organization_logo' => 'nullable|string|max:2048',
        ]);

        foreach (['sitemap_enabled', 'analytics_enabled', 'schema_org_enabled'] as $toggle) {
            $validated[$toggle] = $request->boolean($toggle) ? '1' : '0';
        }

        foreach ($validated as $key => $value) {
            DB::table('seo_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return redirect()->route('admin.seo.index')->with('success', 'SEO settings saved!');
    }

    public function sitemap()
    {
        if (SeoMeta::setting('sitemap_enabled', '1') !== '1') {
            abort(404);
        }

        $urls = [];
        $baseUrl = url('/');

        // Add homepage
        $urls[] = ['loc' => $baseUrl, 'priority' => '1.0', 'changefreq' => 'daily'];

        // Add published pages from CMS
        if (class_exists(\Plugins\CMS\src\Models\Page::class)) {
            $pages = \Plugins\CMS\src\Models\Page::published()->get();
            foreach ($pages as $page) {
                $urls[] = [
                    'loc' => $baseUrl . '/' . $page->slug,
                    'priority' => '0.8',
                    'changefreq' => 'monthly'
                ];
            }
        }

        // Add published posts
        if (class_exists(\Plugins\CMS\src\Models\Post::class)) {
            $posts = \Plugins\CMS\src\Models\Post::published()->get();
            foreach ($posts as $post) {
                $urls[] = [
                    'loc' => $baseUrl . '/post/' . $post->slug,
                    'priority' => '0.7',
                    'changefreq' => 'weekly'
                ];
            }
        }

        return response()->view('plugins.SEO::sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $policy = SeoMeta::setting('robots_policy', 'allow');
        $lines = $policy === 'disallow'
            ? ['User-agent: *', 'Disallow: /']
            : ['User-agent: *', 'Allow: /', 'Sitemap: ' . route('sitemap')];

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain']);
    }

    public function trackingScript()
    {
        if (SeoMeta::setting('analytics_enabled', '1') !== '1') {
            return response('', 204);
        }

        $endpoint = route('seo.track');
        $script = <<<JS
(function(){
  try {
    var key = 'jv_seo_visitor_id';
    var visitor = localStorage.getItem(key);
    if (!visitor) {
      visitor = (crypto && crypto.randomUUID) ? crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2);
      localStorage.setItem(key, visitor);
    }
    var payload = {
      visitor_id: visitor,
      event_type: 'pageview',
      url: location.href,
      title: document.title,
      referrer: document.referrer,
      screen: screen.width + 'x' + screen.height,
      language: navigator.language
    };
    var qs = new URLSearchParams(location.search);
    ['utm_source','utm_medium','utm_campaign'].forEach(function(name){ if(qs.get(name)) payload[name] = qs.get(name); });
    var body = JSON.stringify(payload);
    if (navigator.sendBeacon) {
      navigator.sendBeacon('{$endpoint}', new Blob([body], {type:'application/json'}));
    } else {
      fetch('{$endpoint}', {method:'POST', headers:{'Content-Type':'application/json'}, body:body, keepalive:true});
    }
  } catch (e) {}
})();
JS;

        return response($script, 200, ['Content-Type' => 'application/javascript']);
    }

    public function track(Request $request, SeoAnalyticsService $analytics)
    {
        if (SeoMeta::setting('analytics_enabled', '1') === '1') {
            $analytics->record($request);
        }

        return response()->json(['ok' => true]);
    }

    protected function contentAudit(): array
    {
        $issues = [];

        if (Schema::hasTable('cms_pages')) {
            $pages = DB::table('cms_pages')->select(['title', 'slug', 'meta_title', 'meta_description'])->limit(50)->get();
            foreach ($pages as $page) {
                $score = 100;
                $checks = [];
                if (empty($page->meta_title)) {
                    $score -= 25;
                    $checks[] = 'Missing meta title';
                }
                if (empty($page->meta_description)) {
                    $score -= 30;
                    $checks[] = 'Missing meta description';
                }
                if (strlen((string) $page->title) > 70) {
                    $score -= 10;
                    $checks[] = 'Title may be too long';
                }
                if ($checks) {
                    $issues[] = ['title' => $page->title, 'slug' => $page->slug, 'score' => max(0, $score), 'checks' => $checks];
                }
            }
        }

        return $issues;
    }
}
