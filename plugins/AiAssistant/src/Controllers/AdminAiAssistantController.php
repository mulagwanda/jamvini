<?php

namespace Plugins\AiAssistant\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Plugins\AiAssistant\src\Models\AiConversation;
use Plugins\AiAssistant\src\Models\AiSource;

class AdminAiAssistantController extends Controller
{
    public function index()
    {
        $stats = [
            'sources' => AiSource::count(),
            'ready_sources' => AiSource::ready()->count(),
            'conversations' => AiConversation::count(),
            'escalated' => AiConversation::where('status', 'escalated')->count(),
        ];

        $conversations = AiConversation::with('supportTicket')->latest()->limit(8)->get();

        return view('plugins.AiAssistant::admin.index', compact('stats', 'conversations'));
    }

    public function settings()
    {
        $settings = $this->settingsData();
        $hasGroqKey = env('GROQ_API_KEY') !== null && env('GROQ_API_KEY') !== '';

        return view('plugins.AiAssistant::admin.settings', compact('settings', 'hasGroqKey'));
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'model' => 'required|string|max:120',
            'temperature' => 'required|numeric|min:0|max:1',
            'widget_title' => 'required|string|max:80',
            'welcome_message' => 'required|string|max:500',
            'brand_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'position' => 'required|in:right,left',
            'show_on_public' => 'nullable|boolean',
            'show_on_client' => 'nullable|boolean',
            'kb_enabled' => 'nullable|boolean',
            'require_contact_for_escalation' => 'nullable|boolean',
            'escalation_department' => 'required|string|max:100',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set('ai_assistant_' . $key, is_bool($value) ? (string) (int) $value : (string) $value, 'ai_assistant');
        }

        foreach (['enabled', 'show_on_public', 'show_on_client', 'kb_enabled', 'require_contact_for_escalation'] as $key) {
            Setting::set('ai_assistant_' . $key, $request->boolean($key) ? '1' : '0', 'ai_assistant');
        }

        return back()->with('success', 'AI Assistant settings saved.');
    }

    public function sources()
    {
        $sources = AiSource::latest()->paginate(15);

        return view('plugins.AiAssistant::admin.sources', compact('sources'));
    }

    public function conversations(Request $request)
    {
        $conversations = AiConversation::with(['client', 'supportTicket'])
            ->withCount('messages')
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest('updated_at')
            ->paginate(20);

        return view('plugins.AiAssistant::admin.conversations.index', compact('conversations'));
    }

    public function showConversation(AiConversation $conversation)
    {
        $conversation->load(['client', 'supportTicket', 'messages' => fn ($query) => $query->oldest()]);

        return view('plugins.AiAssistant::admin.conversations.show', compact('conversation'));
    }

    public function replyConversation(Request $request, AiConversation $conversation)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:10000',
            'status' => 'nullable|in:open,human_needed,human_replied,handled,escalated',
        ]);

        $conversation->messages()->create([
            'role' => 'staff',
            'message' => $validated['message'],
            'context' => ['admin_id' => auth('admin')->id()],
        ]);

        $conversation->update([
            'status' => $validated['status'] ?? 'human_replied',
            'last_staff_reply_at' => now(),
        ]);

        return back()->with('success', 'Reply added to the conversation.');
    }

    public function updateConversation(Request $request, AiConversation $conversation)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,human_needed,human_replied,handled,escalated',
        ]);

        $conversation->update($validated);

        return back()->with('success', 'Conversation updated.');
    }

    public function storeSource(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:manual,url,file',
            'title' => 'required|string|max:255',
            'url' => 'nullable|required_if:type,url|url|max:500',
            'content' => 'nullable|required_if:type,manual|string',
            'file' => 'nullable|required_if:type,file|file|max:5120',
        ]);

        $source = new AiSource([
            'type' => $validated['type'],
            'title' => $validated['title'],
            'url' => $validated['url'] ?? null,
            'content' => $validated['content'] ?? null,
            'status' => 'draft',
        ]);

        if ($request->hasFile('file')) {
            $source->file_path = $request->file('file')->store('ai-assistant/sources');
            $source->metadata = [
                'original_name' => $request->file('file')->getClientOriginalName(),
                'mime' => $request->file('file')->getMimeType(),
            ];
        }

        $source->save();
        $this->indexSource($source);

        return back()->with('success', 'Source added and indexed.');
    }

    public function reindexSource(AiSource $source)
    {
        $this->indexSource($source);

        return back()->with('success', 'Source re-indexed.');
    }

    public function destroySource(AiSource $source)
    {
        if ($source->file_path) {
            Storage::delete($source->file_path);
        }

        $source->delete();

        return back()->with('success', 'Source removed.');
    }

    protected function indexSource(AiSource $source): void
    {
        $text = '';

        if ($source->type === 'manual') {
            $text = $source->content ?? '';
        } elseif ($source->type === 'url') {
            $response = Http::timeout(12)->get($source->url);
            $text = $response->successful() ? strip_tags($response->body()) : '';
        } elseif ($source->type === 'file' && $source->file_path) {
            $text = $this->readFileSource($source->file_path);
        }

        $source->update([
            'indexed_text' => trim(preg_replace('/\s+/', ' ', $text)),
            'status' => trim($text) !== '' ? 'ready' : 'failed',
            'last_indexed_at' => now(),
        ]);
    }

    protected function readFileSource(string $path): string
    {
        $fullPath = Storage::path($path);
        $extension = Str::lower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if (!in_array($extension, ['txt', 'md', 'html', 'htm', 'csv', 'json'], true)) {
            return 'Unsupported file type for text indexing. Please upload TXT, MD, HTML, CSV, or JSON for this version.';
        }

        return file_exists($fullPath) ? file_get_contents($fullPath) : '';
    }

    protected function settingsData(): array
    {
        return [
            'enabled' => Setting::get('ai_assistant_enabled', '1'),
            'model' => Setting::get('ai_assistant_model', 'llama-3.3-70b-versatile'),
            'temperature' => Setting::get('ai_assistant_temperature', '0.2'),
            'widget_title' => Setting::get('ai_assistant_widget_title', 'JamVini Assistant'),
            'welcome_message' => Setting::get('ai_assistant_welcome_message', 'Hi, how can I help you today?'),
            'brand_color' => Setting::get('ai_assistant_brand_color', '#2563eb'),
            'position' => Setting::get('ai_assistant_position', 'right'),
            'show_on_public' => Setting::get('ai_assistant_show_on_public', '1'),
            'show_on_client' => Setting::get('ai_assistant_show_on_client', '1'),
            'kb_enabled' => Setting::get('ai_assistant_kb_enabled', '1'),
            'require_contact_for_escalation' => Setting::get('ai_assistant_require_contact_for_escalation', '1'),
            'escalation_department' => Setting::get('ai_assistant_escalation_department', 'Support'),
        ];
    }
}
