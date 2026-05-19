<x-layouts.app :title="$story->title . ' — AI Auto Grader'">
    <div class="page-header">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-heading font-bold text-title-lg">{{ $story->title }}</h1>
                <span
                    class="badge-{{ $story->status === 'approved' ? 'success' : ($story->status === 'draft' ? 'warning' : 'info') }}">
                    {{ ucfirst(str_replace('_', ' ', $story->status)) }}
                </span>
            </div>
            <p class="text-body-sm text-txt-muted mt-1">
                Created by {{ $story->user->name }} &middot; v{{ $story->version }} &middot;
                {{ $story->created_at->format('M d, Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if ($isOwner && $story->isDraft())
                <a href="{{ route('stories.edit', $story) }}" class="btn-secondary">Edit</a>
            @endif

            @if ($story->isDraft() && Auth::user()->hasAnyRole(['admin', 'technical_designer', 'technical_developer']))
                <form method="POST" action="{{ route('stories.approve', $story) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-accent"
                        onclick="return confirm('Approve this story?')">Approve</button>
                </form>
            @endif

            @if ($isOwner && $story->canBeDeleted())
                <form method="POST" action="{{ route('stories.destroy', $story) }}" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger"
                        onclick="return confirm('Delete this story?')">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="page-body">
        {{-- Action Tabs --}}
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('stories.show', $story) }}" class="btn-primary text-caption">Story</a>
            <a href="{{ route('stories.features.index', $story) }}" class="btn-secondary text-caption">Feature
                Lists</a>
            <a href="{{ route('stories.designs.index', $story) }}" class="btn-secondary text-caption">Technical
                Design</a>
            <a href="{{ route('stories.prompts.index', $story) }}" class="btn-secondary text-caption">Coding
                Prompts</a>
            <a href="{{ route('stories.versions', $story) }}" class="btn-secondary text-caption">Versions
                ({{ $story->versions->count() }})</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                @if ($story->description)
                    <div class="card">
                        <h2 class="card-header">Description</h2>
                        <p class="text-body text-txt-secondary whitespace-pre-wrap">{{ $story->description }}</p>
                    </div>
                @endif

                @if ($story->generated_story)
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="card-header mb-0">Generated User Story</h2>
                            <span class="badge-info">AI Generated</span>
                        </div>
                        <div class="prose prose-sm max-w-none text-txt-secondary font-base">
                            {!! nl2br(e($story->generated_story)) !!}
                        </div>
                    </div>
                @endif

                @if ($story->creation_mode === 'quiz' && !$story->generated_story)
                    <div class="card text-center py-12">
                        <svg class="w-16 h-16 text-brand-accent mx-auto mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <h3 class="font-heading font-bold text-title-sm text-txt-primary mb-2">Ready to Generate?</h3>
                        <p class="text-body-sm text-txt-muted mb-6">Complete the quiz and let AI generate your detailed
                            user story.</p>
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('stories.quiz', $story) }}" class="btn-secondary">
                                {{ $story->quizResponses->count() > 0 ? 'Continue Quiz' : 'Start Quiz' }}
                            </a>
                            @if ($story->quizResponses->count() > 0)
                                <form method="POST" action="{{ route('stories.generate', $story) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-primary">Generate User Story</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($story->creation_mode === 'description' && !$story->generated_story)
                    <div class="card">
                        <h2 class="card-header">Generate Story from Description</h2>
                        <form method="POST" action="{{ route('stories.generate', $story) }}">
                            @csrf
                            <button type="submit" class="btn-primary">Generate User Story with AI</button>
                        </form>

                        <div class="mt-6 pt-6 border-t border-border-subtle">
                            <h3 class="font-heading font-semibold text-body mb-3">Or Upload a Document</h3>
                            <form method="POST" action="{{ route('stories.upload', $story) }}"
                                enctype="multipart/form-data" class="flex items-end gap-3">
                                @csrf
                                <div class="flex-1">
                                    <input type="file" name="document" accept=".pdf,.doc,.docx,.txt,.md"
                                        class="form-input" required>
                                    <p class="form-help">PDF, DOC, DOCX, TXT, MD (max 10MB)</p>
                                </div>
                                <button type="submit" class="btn-secondary">Upload & Analyze</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Side Panel --}}
            <div class="space-y-6">
                <div class="card">
                    <h2 class="card-header">Details</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-caption text-txt-muted">Creation Mode</dt>
                            <dd class="text-body-sm font-medium">{{ ucfirst($story->creation_mode) }}</dd>
                        </div>
                        <div>
                            <dt class="text-caption text-txt-muted">Process Type</dt>
                            <dd class="text-body-sm font-medium">
                                {{ $story->process_type === 'single' ? 'Single Process' : 'Multi-Process' }}</dd>
                        </div>
                        @if ($story->selected_domains)
                            <div>
                                <dt class="text-caption text-txt-muted">Domains</dt>
                                <dd class="flex flex-wrap gap-1 mt-1">
                                    @foreach ($story->selected_domains as $domain)
                                        <span class="badge-info">{{ $domain }}</span>
                                    @endforeach
                                </dd>
                            </div>
                        @endif
                        @if ($story->approved_at)
                            <div>
                                <dt class="text-caption text-txt-muted">Approved</dt>
                                <dd class="text-body-sm font-medium">{{ $story->approved_at->format('M d, Y H:i') }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if ($story->isApproved())
                    <div class="card">
                        <h2 class="card-header">Generate Feature List</h2>
                        <form method="POST" action="{{ route('stories.features.generate', $story) }}"
                            class="space-y-3">
                            @csrf
                            <div>
                                <label class="form-label">Format Type</label>
                                <select name="format_type" class="form-input">
                                    <option value="business">Format-01: Business</option>
                                    <option value="procode">Format-02: Pro-Code</option>
                                    <option value="agentic">Format-03: Agentic AI</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-accent w-full">Generate Feature List</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
