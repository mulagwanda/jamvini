<?php

use App\Core\Hooks\Action;
use Plugins\SEO\src\Models\SeoMeta;

// Inject meta tags into the <head>
Action::add('seo.head_tags', function() {
    $title = e(SeoMeta::setting('site_title', config('app.name', 'JamVini Hosting')));
    $desc = e(SeoMeta::setting('site_description', ''));
    
    echo "<title>{$title}</title>\n";
    if ($desc) echo "<meta name=\"description\" content=\"{$desc}\">\n";
    echo "<meta property=\"og:title\" content=\"{$title}\">\n";
    if ($desc) echo "<meta property=\"og:description\" content=\"{$desc}\">\n";
    
    // Google Analytics
    $ga = e(SeoMeta::setting('google_analytics'));
    if ($ga) {
        echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$ga}\"></script>\n";
        echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$ga}');</script>\n";
    }
    
    // Google Verification
    $gv = e(SeoMeta::setting('google_verification'));
    if ($gv) echo "<meta name=\"google-site-verification\" content=\"{$gv}\">\n";

    if (SeoMeta::setting('schema_org_enabled', '1') === '1') {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => SeoMeta::setting('organization_name', config('app.name')),
            'url' => url('/'),
        ];
        if ($logo = SeoMeta::setting('organization_logo')) {
            $schema['logo'] = $logo;
        }
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . "</script>\n";
    }

    if (SeoMeta::setting('analytics_enabled', '1') === '1') {
        echo '<script async src="' . e(route('seo.track.script')) . "\"></script>\n";
    }
});
