<?php

namespace Plugins\AiAssistant\src\Services;

use App\Core\ActivityLogger;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\AiAssistant\src\Models\AiConversation;
use Plugins\AiAssistant\src\Models\AiSource;
use Plugins\Support\src\Models\Ticket;

class AiAssistantService
{
    public function answer(AiConversation $conversation, string $message): array
    {
        $context = $this->contextFor($message);
        $apiKey = (string) env('GROQ_API_KEY', '');

        if ($apiKey === '') {
            return [
                'answer' => $this->fallbackAnswer($context),
                'context' => $context,
                'needs_human' => true,
                'provider' => 'not_configured',
            ];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => Setting::get('ai_assistant_model', 'llama-3.3-70b-versatile'),
                    'temperature' => (float) Setting::get('ai_assistant_temperature', '0.2'),
                    'messages' => $this->messagesForGroq($conversation, $message, $context),
                ]);

            if (!$response->successful()) {
                ActivityLogger::log('ai_assistant.groq_failed', 'AiConversation', $conversation->id, 'Groq request failed.', [
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 500),
                ]);

                return [
                    'answer' => $this->fallbackAnswer($context),
                    'context' => $context,
                    'needs_human' => true,
                    'provider' => 'groq_error',
                ];
            }

            $answer = data_get($response->json(), 'choices.0.message.content');

            return [
                'answer' => $answer ?: $this->fallbackAnswer($context),
                'context' => $context,
                'needs_human' => empty($context),
                'provider' => 'groq',
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'answer' => $this->fallbackAnswer($context),
                'context' => $context,
                'needs_human' => true,
                'provider' => 'exception',
            ];
        }
    }

    public function contextFor(string $message, int $limit = 5): array
    {
        $terms = collect(preg_split('/\W+/', Str::lower($message)))
            ->filter(fn ($term) => strlen($term) >= 3)
            ->unique()
            ->values();

        $sources = collect();

        if (Schema::hasTable('ai_assistant_sources')) {
            $sources = $sources->merge(AiSource::ready()->get()->map(fn ($source) => [
                'type' => $source->type,
                'title' => $source->title,
                'url' => $source->url,
                'text' => $source->indexed_text,
            ]));
        }

        if (Schema::hasTable('kb_articles') && class_exists(\Plugins\KnowledgeBase\src\Models\Article::class)) {
            $sources = $sources->merge(\Plugins\KnowledgeBase\src\Models\Article::published()->get()->map(fn ($article) => [
                'type' => 'knowledge_base',
                'title' => $article->title,
                'url' => url('/kb/' . $article->slug),
                'text' => trim(strip_tags($article->content)),
            ]));
        }

        return $sources
            ->map(function (array $source) use ($terms) {
                $haystack = Str::lower(($source['title'] ?? '') . ' ' . ($source['text'] ?? ''));
                $score = $terms->sum(fn ($term) => Str::contains($haystack, $term) ? 1 : 0);

                return $source + ['score' => $score];
            })
            ->filter(fn ($source) => $source['score'] > 0)
            ->sortByDesc('score')
            ->take($limit)
            ->map(fn ($source) => [
                'type' => $source['type'],
                'title' => $source['title'],
                'url' => $source['url'],
                'excerpt' => Str::limit($source['text'], 900),
                'score' => $source['score'],
            ])
            ->values()
            ->all();
    }

    public function knowledgeBaseSuggestions(string $query = '', int $limit = 5): array
    {
        if (!Schema::hasTable('kb_articles') || !class_exists(\Plugins\KnowledgeBase\src\Models\Article::class)) {
            return [];
        }

        $terms = collect(preg_split('/\W+/', Str::lower($query)))
            ->filter(fn ($term) => strlen($term) >= 3)
            ->unique()
            ->values();

        return \Plugins\KnowledgeBase\src\Models\Article::published()
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($article) use ($terms) {
                $text = trim(strip_tags($article->content));
                $haystack = Str::lower($article->title . ' ' . $text . ' ' . ($article->category ?? ''));
                $score = $terms->isEmpty() ? 1 : $terms->sum(fn ($term) => Str::contains($haystack, $term) ? 1 : 0);

                return [
                    'title' => $article->title,
                    'category' => $article->category,
                    'url' => url('/kb/' . $article->slug),
                    'excerpt' => Str::limit($text, 180),
                    'score' => $score,
                ];
            })
            ->filter(fn ($article) => $article['score'] > 0)
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->all();
    }

    public function escalate(AiConversation $conversation, ?string $message = null): ?Ticket
    {
        if (!Schema::hasTable('support_tickets') || !class_exists(Ticket::class)) {
            return null;
        }

        if ($conversation->support_ticket_id) {
            return $conversation->supportTicket;
        }

        $ticket = Ticket::create([
            'ticket_number' => $this->ticketNumber(),
            'client_id' => $conversation->client_id,
            'department' => Setting::get('ai_assistant_escalation_department', 'Support'),
            'priority' => 'normal',
            'subject' => 'Chat escalation from AI Assistant',
            'status' => 'open',
            'source' => 'ai_assistant',
            'metadata' => [
                'conversation_id' => $conversation->id,
                'page_url' => $conversation->page_url,
                'page_title' => $conversation->page_title,
                'country' => $conversation->country_name ?: $conversation->country_code,
                'visitor_email' => $conversation->visitor_email,
            ],
            'last_reply_at' => now(),
        ]);

        $transcript = $conversation->messages()
            ->oldest()
            ->get()
            ->map(fn ($item) => ucfirst($item->role) . ': ' . $item->message)
            ->implode("\n\n");

        $details = collect([
            'Visitor' => $conversation->visitor_name ?: 'Visitor',
            'Email' => $conversation->visitor_email ?: '-',
            'Page' => $conversation->page_title ? $conversation->page_title . ' - ' . $conversation->page_url : $conversation->page_url,
            'Country' => $conversation->country_name ?: $conversation->country_code,
            'IP' => $conversation->metadata['ip'] ?? null,
        ])->filter()->map(fn ($value, $key) => "{$key}: {$value}")->implode("\n");

        $ticket->replies()->create([
            'client_id' => $conversation->client_id,
            'author_type' => $conversation->client_id ? 'client' : 'visitor',
            'message' => trim(($message ? $message . "\n\n" : '') . "Conversation context:\n{$details}\n\nChat transcript:\n\n" . $transcript),
        ]);

        $conversation->update([
            'status' => 'escalated',
            'support_ticket_id' => $ticket->id,
            'escalated_at' => now(),
        ]);

        return $ticket;
    }

    protected function messagesForGroq(AiConversation $conversation, string $message, array $context): array
    {
        $businessName = Setting::get('company_name', 'JamVini Hosting');
        $contextText = collect($context)
            ->map(fn ($item) => "Source: {$item['title']}\n{$item['excerpt']}")
            ->implode("\n\n---\n\n");

        $messages = [
            [
                'role' => 'system',
                'content' => "You are the support assistant for {$businessName}. Use the provided JamVini knowledge context and the recent chat history. If the context does not answer the question, say you can connect the visitor to human support. Be concise, friendly, and accurate.",
            ],
            [
                'role' => 'user',
                'content' => "Knowledge context for the latest visitor message:\n" . ($contextText ?: 'No relevant context found.'),
            ],
        ];

        $history = $conversation->messages()
            ->latest()
            ->limit(12)
            ->get()
            ->reverse()
            ->map(fn ($item) => [
                'role' => $item->role === 'assistant' ? 'assistant' : 'user',
                'content' => Str::limit($item->message, 1200),
            ])
            ->values()
            ->all();

        return array_merge($messages, $history);
    }

    protected function fallbackAnswer(array $context): string
    {
        if (!empty($context)) {
            return 'I found some related information, but I cannot fully answer with confidence right now. I can connect you with our support team and include this chat transcript.';
        }

        return 'I am not confident enough to answer that from the current knowledge base. I can connect you with our support team so a human can help.';
    }

    protected function ticketNumber(): string
    {
        $prefix = 'TKT-' . now()->format('Y') . '-';
        $sequence = Ticket::withTrashed()->where('ticket_number', 'like', $prefix . '%')->count() + 1;

        do {
            $number = $prefix . str_pad((string) $sequence++, 5, '0', STR_PAD_LEFT);
        } while (Ticket::withTrashed()->where('ticket_number', $number)->exists());

        return $number;
    }
}
