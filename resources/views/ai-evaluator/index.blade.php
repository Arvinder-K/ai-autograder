<x-layouts.app :title="'AI Assignment Evaluator'">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">AI Assignment Evaluator</h1>
            <p class="text-body-sm text-txt-muted mt-1">Upload the prompt and submission ZIP to generate an AI evaluation report.</p>
        </div>
    </div>

    <div class="page-body grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-1">
            <div class="card-header">Upload Assignment</div>
            <form method="POST" action="{{ route('ai.evaluator.process') }}" enctype="multipart/form-data" x-data="{ loading: false }" @submit.prevent="loading = true; $event.target.submit();">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="form-label" for="student_name">Student Name</label>
                        <input id="student_name" type="text" name="student_name" value="{{ old('student_name') }}" class="form-input w-full" required>
                    </div>

                    <div>
                        <label class="form-label" for="assignment_name">Assignment Name</label>
                        <input id="assignment_name" type="text" name="assignment_name" value="{{ old('assignment_name') }}" class="form-input w-full" required>
                    </div>

                    <div>
                        <label class="form-label" for="prompt_file">Prompt File</label>
                        <input id="prompt_file" type="file" name="prompt_file" accept=".txt,.pdf,.docx" class="form-input w-full" required>
                    </div>

                    <div>
                        <label class="form-label" for="zip_file">ZIP File</label>
                        <input id="zip_file" type="file" name="zip_file" accept=".zip" class="form-input w-full" required>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <button type="submit" class="btn-primary flex items-center justify-center gap-2 w-full" :disabled="loading">
                            <span x-show="!loading">Generate Evaluation</span>
                            <span x-show="loading" class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card lg:col-span-2">
            <div class="card-header">Recent Evaluations</div>

            @if ($evaluations->isEmpty())
                <div class="text-center py-12">
                    <p class="text-body-sm text-txt-muted">No AI evaluations have been generated yet.</p>
                </div>
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($evaluations as $evaluation)
                        <a href="{{ route('ai.evaluator.show', $evaluation) }}" class="block p-4 hover:bg-surface-panel-muted transition-colors">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-heading font-semibold text-body text-txt-primary">{{ $evaluation->assignment_name }}</p>
                                    <p class="text-caption text-txt-muted mt-1">{{ $evaluation->student_name }} &middot; {{ $evaluation->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="badge-{{ $evaluation->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($evaluation->status) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
