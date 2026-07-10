@if(\App\Models\Setting::get('ai_assistant_enabled', '1') === '1' && \Illuminate\Support\Facades\Route::has('ai-assistant.message'))
    <link rel="stylesheet" href="{{ asset('plugins/ai-assistant/widget.css') }}?v={{ filemtime(public_path('plugins/ai-assistant/widget.css')) }}">
    <script>
        window.JamViniAiAssistant = {
            configUrl: @json(route('ai-assistant.config')),
            messageUrl: @json(route('ai-assistant.message')),
            escalateUrl: @json(route('ai-assistant.escalate')),
            conversationUrl: @json(url('/ai-assistant/conversation')),
            csrfToken: @json(csrf_token())
        };
    </script>
    <script src="{{ asset('plugins/ai-assistant/widget.js') }}?v={{ filemtime(public_path('plugins/ai-assistant/widget.js')) }}" defer></script>
@endif
