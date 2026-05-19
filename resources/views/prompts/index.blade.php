<x-layouts.app :title="'Coding Prompts — ' . $story->title">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">AI Coding Prompts</h1>
            <p class="text-body-sm text-txt-muted mt-1">{{ $story->title }} — Micro-prompts optimized for AI credit
                efficiency</p>
        </div>
        <a href="{{ route('stories.show', $story) }}" class="btn-secondary">Back to Story</a>
    </div>

    <div class="page-body">
        @if ($story->featureLists->count() > 0)
            <div class="card mb-6">
                <h2 class="card-header">Generate Coding Prompts</h2>
                <form method="POST" action="{{ route('stories.prompts.generate', $story) }}"
                    class="flex items-end gap-4">
                    @csrf
                    <div class="flex-1">
                        <label class="form-label">Target AI Tool</label>
                        <select name="target_tool" class="form-input">
                            <option value="claude_ai">Claude AI</option>
                            <option value="github_copilot">GitHub Copilot</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-accent">Generate Micro-Prompts</button>
                </form>
            </div>
        @endif

        @if ($prompts->isEmpty())
            <div class="card text-center py-12">
                <p class="text-txt-muted">No coding prompts generated yet.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($prompts as $prompt)
                    <div class="card">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-8 h-8 rounded-full bg-brand-primary text-txt-inverse flex items-center justify-center font-heading font-bold text-body-sm">
                                    {{ $prompt->prompt_sequence }}
                                </span>
                                <div>
                                    <h3 class="font-heading font-semibold text-body">{{ $prompt->title }}</h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span
                                            class="badge-info">{{ str_replace('_', ' ', ucfirst($prompt->target_tool)) }}</span>
                                        @if ($prompt->is_continuation)
                                            <span class="badge-warning">Continuation</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button
                                onclick="navigator.clipboard.writeText(this.closest('.card').querySelector('pre').textContent)"
                                class="btn-ghost text-caption">
                                Copy
                            </button>
                        </div>
                        <pre
                            class="bg-surface-page p-4 rounded-lg text-body-sm font-mono text-txt-secondary overflow-x-auto whitespace-pre-wrap max-h-64 overflow-y-auto">{{ $prompt->prompt_content }}</pre>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
