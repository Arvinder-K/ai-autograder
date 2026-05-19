<x-layouts.app :title="'Quiz — ' . $story->title">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">Requirements Quiz</h1>
            <p class="text-body-sm text-txt-muted mt-1">{{ $story->title }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stories.show', $story) }}" class="btn-secondary">Back to Story</a>
            @if (count($existingResponses) > 0)
                <form method="POST" action="{{ route('stories.generate', $story) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-accent">Generate User Story</button>
                </form>
            @endif
        </div>
    </div>

    <div class="page-body" x-data="quizWizard()">
        <div class="flex gap-6">
            {{-- Section Navigation --}}
            <div class="hidden lg:block w-64 flex-shrink-0">
                <div class="card sticky top-24 p-3">
                    <p
                        class="px-3 py-2 text-caption font-heading font-semibold text-txt-muted uppercase tracking-wider">
                        Sections</p>
                    @foreach ($sections as $index => $section)
                        <button @click="currentSection = {{ $index }}" class="quiz-step w-full text-left"
                            :class="{
                                'active': currentSection === {{ $index }},
                                'completed': sectionCompleted({{ $index }})
                            }">
                            <span
                                class="w-6 h-6 rounded-full flex items-center justify-center text-caption font-heading font-bold flex-shrink-0"
                                :class="currentSection === {{ $index }} ? 'bg-brand-primary text-txt-inverse' :
                                    'bg-surface-panel-muted text-txt-muted'">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-body-sm truncate">{{ $section->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Quiz Content --}}
            <div class="flex-1">
                <form method="POST" action="{{ route('stories.quiz.save', $story) }}">
                    @csrf

                    @foreach ($sections as $sIndex => $section)
                        <div x-show="currentSection === {{ $sIndex }}" x-cloak>
                            <div class="card mb-6">
                                <div class="flex items-center gap-3 mb-1">
                                    <span
                                        class="w-8 h-8 rounded-full bg-brand-primary text-txt-inverse flex items-center justify-center font-heading font-bold text-body-sm">{{ $sIndex + 1 }}</span>
                                    <h2 class="font-heading font-bold text-title">{{ $section->name }}</h2>
                                </div>
                                @if ($section->description)
                                    <p class="text-body-sm text-txt-muted ml-11 mb-6">{{ $section->description }}</p>
                                @endif

                                <div class="space-y-6 mt-6">
                                    @foreach ($section->questions as $question)
                                        <div class="border-b border-border-subtle pb-5 last:border-0 last:pb-0">
                                            <label class="form-label">
                                                {{ $question->question_text }}
                                                @if ($question->is_required)
                                                    <span class="text-status-danger-text">*</span>
                                                @endif
                                            </label>

                                            @if ($question->help_text)
                                                <p class="form-help mb-2">{{ $question->help_text }}</p>
                                            @endif

                                            @switch($question->question_type)
                                                @case('text')
                                                    <input type="text" name="responses[{{ $question->id }}]"
                                                        value="{{ $existingResponses[$question->id] ?? '' }}"
                                                        class="form-input" {{ $question->is_required ? 'required' : '' }}>
                                                @break

                                                @case('textarea')
                                                    <textarea name="responses[{{ $question->id }}]" rows="3" class="form-input"
                                                        {{ $question->is_required ? 'required' : '' }}>{{ $existingResponses[$question->id] ?? '' }}</textarea>
                                                @break

                                                @case('single_choice')
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                                                        @foreach ($question->options ?? [] as $option)
                                                            <label
                                                                class="flex items-center gap-2 p-3 rounded-md border border-border cursor-pointer hover:border-brand-primary transition-colors">
                                                                <input type="radio" name="responses[{{ $question->id }}]"
                                                                    value="{{ $option }}"
                                                                    {{ ($existingResponses[$question->id] ?? '') === $option ? 'checked' : '' }}>
                                                                <span class="text-body-sm">{{ $option }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @break

                                                @case('multi_choice')
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                                                        @foreach ($question->options ?? [] as $option)
                                                            @if ($option !== '__dynamic_domains__' && $option !== '__dynamic_business_units__')
                                                                <label
                                                                    class="flex items-center gap-2 p-3 rounded-md border border-border cursor-pointer hover:border-brand-primary transition-colors">
                                                                    <input type="checkbox"
                                                                        name="responses[{{ $question->id }}][]"
                                                                        value="{{ $option }}">
                                                                    <span class="text-body-sm">{{ $option }}</span>
                                                                </label>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @break

                                                @case('yes_no')
                                                    <div class="flex gap-4 mt-2">
                                                        <label
                                                            class="flex items-center gap-2 p-3 rounded-md border border-border cursor-pointer hover:border-brand-primary transition-colors">
                                                            <input type="radio" name="responses[{{ $question->id }}]"
                                                                value="Yes"
                                                                {{ ($existingResponses[$question->id] ?? '') === 'Yes' ? 'checked' : '' }}>
                                                            <span class="text-body-sm">Yes</span>
                                                        </label>
                                                        <label
                                                            class="flex items-center gap-2 p-3 rounded-md border border-border cursor-pointer hover:border-brand-primary transition-colors">
                                                            <input type="radio" name="responses[{{ $question->id }}]"
                                                                value="No"
                                                                {{ ($existingResponses[$question->id] ?? '') === 'No' ? 'checked' : '' }}>
                                                            <span class="text-body-sm">No</span>
                                                        </label>
                                                    </div>
                                                @break

                                                @case('dropdown')
                                                @case('multi_dropdown')
                                                    <select
                                                        name="responses[{{ $question->id }}]{{ $question->question_type === 'multi_dropdown' ? '[]' : '' }}"
                                                        class="form-input"
                                                        {{ $question->question_type === 'multi_dropdown' ? 'multiple' : '' }}>
                                                        <option value="">Select...</option>
                                                        @foreach ($question->options ?? [] as $option)
                                                            @if ($option !== '__dynamic_domains__')
                                                                <option value="{{ $option }}">{{ $option }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @break

                                                @case('number')
                                                    <input type="number" name="responses[{{ $question->id }}]"
                                                        value="{{ $existingResponses[$question->id] ?? '' }}"
                                                        class="form-input w-32">
                                                @break

                                                @case('scale')
                                                    <div class="flex gap-2 mt-2">
                                                        @for ($i = 1; $i <= 10; $i++)
                                                            <label class="flex flex-col items-center gap-1 cursor-pointer">
                                                                <input type="radio" name="responses[{{ $question->id }}]"
                                                                    value="{{ $i }}"
                                                                    {{ ($existingResponses[$question->id] ?? '') == $i ? 'checked' : '' }}>
                                                                <span class="text-caption">{{ $i }}</span>
                                                            </label>
                                                        @endfor
                                                    </div>
                                                @break
                                            @endswitch
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Navigation Buttons --}}
                            <div class="flex items-center justify-between">
                                <button type="button" @click="prevSection()" class="btn-secondary"
                                    :class="{ 'invisible': currentSection === 0 }">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Previous
                                </button>

                                <button type="submit" class="btn-secondary">Save Progress</button>

                                <button type="button" @click="nextSection()" class="btn-primary"
                                    x-show="currentSection < {{ count($sections) - 1 }}">
                                    Next
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>

                                <button type="submit" class="btn-accent"
                                    x-show="currentSection === {{ count($sections) - 1 }}">
                                    Complete Quiz
                                </button>
                            </div>
                        </div>
                    @endforeach
                </form>
            </div>
        </div>
    </div>

    <script>
        function quizWizard() {
            return {
                currentSection: 0,
                totalSections: {{ count($sections) }},
                nextSection() {
                    if (this.currentSection < this.totalSections - 1) this.currentSection++;
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                },
                prevSection() {
                    if (this.currentSection > 0) this.currentSection--;
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                },
                sectionCompleted(index) {
                    return false; // Can enhance with response tracking
                },
            };
        }
    </script>
</x-layouts.app>
