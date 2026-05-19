<x-layouts.app :title="$prompt->title">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">{{ $prompt->title }}</h1>
            <p class="text-body-sm text-txt-muted mt-1">Prompt #{{ $prompt->prompt_sequence }} for
                {{ str_replace('_', ' ', ucfirst($prompt->target_tool)) }}</p>
        </div>
        <a href="{{ route('stories.prompts.index', $story) }}" class="btn-secondary">Back</a>
    </div>

    <div class="page-body max-w-4xl">
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-header mb-0">Prompt Content</h2>
                <button onclick="navigator.clipboard.writeText(document.getElementById('prompt-content').textContent)"
                    class="btn-accent text-caption">Copy to Clipboard</button>
            </div>
            <pre id="prompt-content"
                class="bg-surface-page p-6 rounded-lg text-body-sm font-mono text-txt-secondary whitespace-pre-wrap">{{ $prompt->prompt_content }}</pre>
        </div>

        @if ($prompt->continuation_context)
            <div class="card mt-6">
                <h2 class="card-header">Continuation Context</h2>
                <pre class="bg-surface-page p-4 rounded-lg text-body-sm font-mono text-txt-muted whitespace-pre-wrap">{{ $prompt->continuation_context }}</pre>
            </div>
        @endif
    </div>
</x-layouts.app>
