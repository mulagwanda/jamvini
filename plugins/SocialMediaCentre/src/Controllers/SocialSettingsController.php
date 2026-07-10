<?php

namespace Plugins\SocialMediaCentre\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SocialSettingsController extends Controller
{
    protected array $platforms = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'x' => 'X',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
    ];

    public function edit()
    {
        return view('plugins.SocialMediaCentre::admin.settings', [
            'settings' => $this->settings(),
            'platforms' => $this->platforms,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_platforms' => 'nullable|array',
            'default_platforms.*' => 'string|max:40',
            'default_hashtags' => 'nullable|string|max:1000',
            'default_status' => 'required|in:draft,ready,scheduled',
            'timezone' => 'required|timezone',
            'ai_tone' => 'required|string|max:160',
            'ai_default_brief' => 'nullable|string|max:2000',
            'brand_voice' => 'nullable|string|max:2000',
            'approval_required' => 'nullable|boolean',
            'utm_enabled' => 'nullable|boolean',
            'utm_source' => 'nullable|string|max:80',
            'utm_medium' => 'nullable|string|max:80',
            'utm_campaign_prefix' => 'nullable|string|max:80',
        ]);

        $values = [
            'default_platforms' => implode(',', array_values(array_intersect($validated['default_platforms'] ?? [], array_keys($this->platforms)))),
            'default_hashtags' => $validated['default_hashtags'] ?? '',
            'default_status' => $validated['default_status'],
            'timezone' => $validated['timezone'],
            'ai_tone' => $validated['ai_tone'],
            'ai_default_brief' => $validated['ai_default_brief'] ?? '',
            'brand_voice' => $validated['brand_voice'] ?? '',
            'approval_required' => $request->boolean('approval_required') ? '1' : '0',
            'utm_enabled' => $request->boolean('utm_enabled') ? '1' : '0',
            'utm_source' => $validated['utm_source'] ?? '',
            'utm_medium' => $validated['utm_medium'] ?? '',
            'utm_campaign_prefix' => $validated['utm_campaign_prefix'] ?? '',
        ];

        foreach ($values as $key => $value) {
            Setting::set('social_' . $key, (string) $value, 'social_media_centre', ucwords(str_replace('_', ' ', $key)));
        }

        return back()->with('success', 'Social Centre settings saved.');
    }

    public function settings(): array
    {
        return [
            'default_platforms' => array_filter(explode(',', Setting::get('social_default_platforms', 'facebook,instagram,linkedin'))),
            'default_hashtags' => Setting::get('social_default_hashtags', '#webhosting #domains #digitalbusiness'),
            'default_status' => Setting::get('social_default_status', 'draft'),
            'timezone' => Setting::get('social_timezone', Setting::get('timezone', config('app.timezone'))),
            'ai_tone' => Setting::get('social_ai_tone', 'professional, friendly, clear'),
            'ai_default_brief' => Setting::get('social_ai_default_brief', ''),
            'brand_voice' => Setting::get('social_brand_voice', ''),
            'approval_required' => Setting::get('social_approval_required', '0'),
            'utm_enabled' => Setting::get('social_utm_enabled', '0'),
            'utm_source' => Setting::get('social_utm_source', 'social'),
            'utm_medium' => Setting::get('social_utm_medium', 'organic'),
            'utm_campaign_prefix' => Setting::get('social_utm_campaign_prefix', 'jamvini'),
        ];
    }
}
