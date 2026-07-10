<?php

namespace Plugins\AiAssistant\src\Controllers;

use App\Core\Hooks\Action;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\AiAssistant\src\Models\AiConversation;
use Plugins\AiAssistant\src\Services\AiAssistantService;

class WidgetController extends Controller
{
    public function config()
    {
        return response()->json($this->publicConfig());
    }

    public function conversation(Request $request, AiConversation $conversation)
    {
        abort_unless($conversation->public_token === $request->query('token'), 404);

        return response()->json([
            'conversation_id' => $conversation->id,
            'conversation_token' => $conversation->public_token,
            'status' => $conversation->status,
            'messages' => $conversation->messages()
                ->oldest()
                ->get()
                ->map(fn ($message) => [
                    'id' => $message->id,
                    'role' => in_array($message->role, ['user', 'staff'], true) ? $message->role : 'assistant',
                    'message' => $message->message,
                    'sources' => collect($message->context ?? [])->map(fn ($item) => [
                        'title' => $item['title'] ?? null,
                        'url' => $item['url'] ?? null,
                    ])->filter(fn ($item) => $item['title'])->values(),
                    'created_at' => $message->created_at?->toISOString(),
                ])
                ->values(),
        ]);
    }

    public function knowledgeBase(Request $request, AiAssistantService $assistant)
    {
        abort_unless($this->knowledgeBaseEnabled(), 404);

        $validated = $request->validate([
            'q' => 'nullable|string|max:180',
        ]);

        return response()->json([
            'enabled' => true,
            'articles' => $assistant->knowledgeBaseSuggestions($validated['q'] ?? '', 5),
        ]);
    }

    public function message(Request $request, AiAssistantService $assistant)
    {
        abort_unless(Setting::get('ai_assistant_enabled', '1') === '1', 403);

        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'conversation_token' => 'nullable|string|max:80',
            'message' => 'required|string|max:2000',
            'visitor_name' => 'nullable|string|max:120',
            'visitor_email' => 'nullable|email|max:180',
            'page_url' => 'nullable|string|max:2000',
            'page_title' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:2000',
            'timezone' => 'nullable|string|max:120',
            'language' => 'nullable|string|max:50',
        ]);

        $conversation = $this->resolveConversation($validated);
        $this->refreshConversationContext($conversation, $validated);
        if ($conversation->status === 'handled') {
            $conversation->update(['status' => 'open']);
        }

        $conversation->messages()->create([
            'role' => 'user',
            'message' => $validated['message'],
            'context' => [
                'page_url' => $conversation->page_url,
                'page_title' => $conversation->page_title,
                'country' => $conversation->country_name,
            ],
        ]);

        $answer = $assistant->answer($conversation, $validated['message']);

        if ($answer['needs_human'] && !in_array($conversation->status, ['escalated', 'handled'], true)) {
            $conversation->update(['status' => 'human_needed']);
        }

        $conversation->messages()->create([
            'role' => 'assistant',
            'message' => $answer['answer'],
            'context' => $answer['context'],
        ]);

        Action::do('ai_assistant.message_received', $conversation, $validated['message']);

        return response()->json([
            'conversation_id' => $conversation->id,
            'conversation_token' => $conversation->public_token,
            'status' => $conversation->status,
            'reply' => $answer['answer'],
            'needs_human' => $answer['needs_human'],
            'sources' => collect($answer['context'])->map(fn ($item) => [
                'title' => $item['title'],
                'url' => $item['url'],
            ])->values(),
        ]);
    }

    public function escalate(Request $request, AiAssistantService $assistant)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|integer|exists:ai_assistant_conversations,id',
            'conversation_token' => 'required|string|max:80',
            'visitor_name' => 'nullable|string|max:120',
            'visitor_email' => 'nullable|email|max:180',
            'message' => 'nullable|string|max:2000',
        ]);

        $conversation = AiConversation::whereKey($validated['conversation_id'])
            ->where('public_token', $validated['conversation_token'])
            ->firstOrFail();
        $conversation->update([
            'visitor_name' => $validated['visitor_name'] ?? $conversation->visitor_name,
            'visitor_email' => $validated['visitor_email'] ?? $conversation->visitor_email,
        ]);

        if (Setting::get('ai_assistant_require_contact_for_escalation', '1') === '1' && !$conversation->client_id && !$conversation->visitor_email) {
            return response()->json(['message' => 'Please enter your email so support can reply.'], 422);
        }

        $ticket = $assistant->escalate($conversation, $validated['message'] ?? null);
        Action::do('ai_assistant.escalated', $conversation, $ticket);

        return response()->json([
            'message' => $ticket
                ? 'A support ticket has been opened. Our team will follow up soon.'
                : 'Human escalation is not available because the Support plugin is not ready.',
            'ticket_number' => $ticket?->ticket_number,
            'status' => $conversation->fresh()->status,
        ]);
    }

    protected function resolveConversation(array $data): AiConversation
    {
        if (!empty($data['conversation_id'])) {
            $conversation = AiConversation::whereKey($data['conversation_id'])
                ->where('public_token', $data['conversation_token'] ?? '')
                ->first();

            if ($conversation) {
                return $conversation;
            }
        }

        $client = Auth::guard('web')->user();

        return AiConversation::create([
            'public_token' => Str::random(48),
            'client_id' => $client?->id,
            'visitor_name' => $data['visitor_name'] ?? null,
            'visitor_email' => $data['visitor_email'] ?? null,
            'page_url' => $data['page_url'] ?? request()->headers->get('referer'),
            'page_title' => $data['page_title'] ?? null,
            'country_code' => $this->countryCode(),
            'country_name' => $this->countryName($this->countryCode()),
            'status' => 'open',
            'metadata' => [
                'ip' => request()->ip(),
                'referrer' => $data['referrer'] ?? request()->headers->get('referer'),
                'user_agent' => request()->userAgent(),
                'timezone' => $data['timezone'] ?? null,
                'language' => $data['language'] ?? request()->headers->get('accept-language'),
            ],
        ]);
    }

    protected function refreshConversationContext(AiConversation $conversation, array $data): void
    {
        $metadata = array_merge($conversation->metadata ?? [], [
            'ip' => request()->ip(),
            'referrer' => $data['referrer'] ?? ($conversation->metadata['referrer'] ?? request()->headers->get('referer')),
            'user_agent' => request()->userAgent(),
            'timezone' => $data['timezone'] ?? ($conversation->metadata['timezone'] ?? null),
            'language' => $data['language'] ?? ($conversation->metadata['language'] ?? request()->headers->get('accept-language')),
            'last_seen_at' => now()->toISOString(),
        ]);

        $code = $conversation->country_code ?: $this->countryCode();

        $conversation->update([
            'visitor_name' => $data['visitor_name'] ?? $conversation->visitor_name,
            'visitor_email' => $data['visitor_email'] ?? $conversation->visitor_email,
            'page_url' => $data['page_url'] ?? $conversation->page_url,
            'page_title' => $data['page_title'] ?? $conversation->page_title,
            'country_code' => $code,
            'country_name' => $conversation->country_name ?: $this->countryName($code),
            'metadata' => $metadata,
        ]);
    }

    protected function publicConfig(): array
    {
        return [
            'enabled' => Setting::get('ai_assistant_enabled', '1') === '1',
            'title' => Setting::get('ai_assistant_widget_title', 'JamVini Assistant'),
            'welcomeMessage' => Setting::get('ai_assistant_welcome_message', 'Hi, how can I help you today?'),
            'brandColor' => Setting::get('ai_assistant_brand_color', '#2563eb'),
            'position' => Setting::get('ai_assistant_position', 'right'),
            'requireContactForEscalation' => Setting::get('ai_assistant_require_contact_for_escalation', '1') === '1',
            'knowledgeBaseEnabled' => $this->knowledgeBaseEnabled(),
            'knowledgeBaseUrl' => route('ai-assistant.knowledge-base'),
            'pollSeconds' => 12,
        ];
    }

    protected function knowledgeBaseEnabled(): bool
    {
        return Setting::get('ai_assistant_kb_enabled', '1') === '1'
            && Schema::hasTable('kb_articles')
            && class_exists(\Plugins\KnowledgeBase\src\Models\Article::class);
    }

    protected function countryCode(): ?string
    {
        $headers = ['CF-IPCountry', 'CloudFront-Viewer-Country', 'X-AppEngine-Country'];

        foreach ($headers as $header) {
            $value = strtoupper((string) request()->headers->get($header));
            if (preg_match('/^[A-Z]{2}$/', $value) && $value !== 'XX') {
                return $value;
            }
        }

        return null;
    }

    protected function countryName(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        return [
            'TZ' => 'Tanzania', 'KE' => 'Kenya', 'UG' => 'Uganda', 'RW' => 'Rwanda',
            'BI' => 'Burundi', 'ZA' => 'South Africa', 'NG' => 'Nigeria', 'GH' => 'Ghana',
            'GB' => 'United Kingdom', 'US' => 'United States', 'IN' => 'India',
        ][$code] ?? $code;
    }
}
