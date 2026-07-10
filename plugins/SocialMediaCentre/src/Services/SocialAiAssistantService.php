<?php

namespace Plugins\SocialMediaCentre\src\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SocialAiAssistantService
{
    public function generate(string $action, array $payload): array
    {
        $apiKey = (string) env('GROQ_API_KEY', '');

        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'GROQ_API_KEY is not configured.',
            ];
        }

        $response = Http::withToken($apiKey)
            ->timeout(45)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => env('SOCIAL_AI_MODEL', 'llama-3.3-70b-versatile'),
                'temperature' => 0.65,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a senior social media copywriter for web hosting companies. Write clear, practical, conversion-focused copy. Return only the requested text, no markdown headings.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->prompt($action, $payload),
                    ],
                ],
            ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'AI request failed: ' . Str::limit($response->body(), 220),
            ];
        }

        $content = trim((string) data_get($response->json(), 'choices.0.message.content'));

        return [
            'success' => $content !== '',
            'message' => $content !== '' ? 'AI suggestion ready.' : 'AI did not return a suggestion.',
            'content' => $content,
        ];
    }

    protected function prompt(string $action, array $payload): string
    {
        $title = $payload['title'] ?: 'Untitled social post';
        $caption = $payload['caption'] ?: '';
        $hashtags = $payload['hashtags'] ?: '';
        $brief = $payload['brief'] ?: '';
        $platforms = implode(', ', $payload['platforms'] ?: ['facebook']);
        $tone = $payload['tone'] ?: 'professional, friendly, clear';
        $brandVoice = Setting::get('social_brand_voice', '');

        $context = "Title: {$title}\nCreative brief: {$brief}\nBrand voice: {$brandVoice}\nPlatforms: {$platforms}\nTone: {$tone}\nCurrent caption: {$caption}\nCurrent hashtags: {$hashtags}";

        return match ($action) {
            'generate_caption' => "{$context}\n\nWrite a complete social media caption for this post. Keep it useful for a web hosting company. Include a clear call to action.",
            'improve_caption' => "{$context}\n\nImprove the current caption. Make it clearer, more persuasive, and still natural. Keep the meaning.",
            'shorten_for_x' => "{$context}\n\nRewrite the caption for X/Twitter. Keep it under 260 characters, clear, and action-oriented.",
            'generate_hashtags' => "{$context}\n\nGenerate 8 to 12 relevant hashtags only. Separate them with spaces. Avoid jokes and unrelated trends.",
            'image_prompt' => "{$context}\n\nWrite one strong AI image generation prompt for a clean promotional image. Mention web hosting, modern business, and visual style. Do not include hashtags.",
            default => "{$context}\n\nSuggest an improved social media caption.",
        };
    }
}
