<x-layouts.app :title="'AI Evaluation Result'">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">AI Evaluation Result</h1>
            <p class="text-body-sm text-txt-muted mt-1">Review the evaluation and download the report as PDF or DOCX.</p>
        </div>

        @unless(isset($forPdf) && $forPdf)
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('ai.evaluator.download.pdf', $evaluation) }}" class="btn-secondary" download>Download Report (.txt)</a>
                <a href="{{ route('ai.evaluator.download.docx', $evaluation) }}" class="btn-secondary" download>Download Report (.docx)</a>
                <a href="{{ route('ai.evaluator') }}" class="btn-ghost">Back to Evaluator</a>
            </div>
        @endunless
    </div>

    <div class="page-body grid gap-6">
        <div class="card">
            <div class="card-header">Assignment Details</div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Student Name</p>
                    <p class="font-semibold text-body text-txt-primary">{{ $evaluation->student_name }}</p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Assignment Title</p>
                    <p class="font-semibold text-body text-txt-primary">{{ $evaluationData['assignment_title'] ?? $evaluation->assignment_name }}</p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Status</p>
                    <p class="font-semibold text-body text-txt-primary">{{ ucfirst($evaluation->status) }}</p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Created</p>
                    <p class="font-semibold text-body text-txt-primary">{{ $evaluation->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Prompt File</p>
                    <p class="font-semibold text-body text-txt-primary truncate" title="{{ basename($evaluation->prompt_file) }}">{{ basename($evaluation->prompt_file) }}</p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Submission File</p>
                    <p class="font-semibold text-body text-txt-primary truncate" title="{{ basename($evaluation->zip_file) }}">{{ basename($evaluation->zip_file) }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">AI Evaluation Report</div>
            @if (!empty($isJson) && is_array($evaluationData))
                <div class="space-y-6">
                    @if (isset($evaluationData['error']))
                        <div class="p-4 border border-red-100 bg-red-50 rounded-lg">
                            <p class="text-sm font-semibold text-red-800 uppercase mb-2">AI Service Error</p>
                            <p class="text-sm text-red-900">{{ $evaluationData['learner_feedback'] ?? 'An error occurred.' }}</p>
                            <p class="text-xs text-red-700 mt-2">Technical details: {{ $evaluationData['error'] }}</p>
                        </div>
                    @endif

                    @if (!empty($evaluationData['summary']))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-caption text-txt-muted uppercase tracking-wide">⭐ Score</p>
                                <p class="text-2xl font-bold text-txt-primary">{{ $evaluationData['summary']['earned_score'] ?? '0' }} / {{ $evaluationData['summary']['max_score'] ?? '100' }}</p>
                            </div>
                            <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-lg">
                                <p class="text-caption text-indigo-800 font-bold uppercase tracking-wide">KSA Index</p>
                                <p class="text-2xl font-bold text-indigo-900">{{ $evaluationData['summary']['ksa_index'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @endif

                    @if (!empty($evaluationData['grading_criteria']))
                        <div class="space-y-6">
                            @foreach ($evaluationData['grading_criteria'] as $item)
                                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $item['criteria'] }}</h3>
                                    
                                    <div class="space-y-5">
                                        {{-- Feedback --}}
                                        <div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                                <span class="font-medium text-gray-900">Feedback</span>
                                            </div>
                                            <p class="text-gray-700 leading-relaxed text-sm">{{ $item['feedback'] }}</p>
                                        </div>

                                        {{-- Suggestion --}}
                                        <div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                                </svg>
                                                <span class="font-medium text-gray-900">Suggestion</span>
                                            </div>
                                            <p class="text-gray-700 leading-relaxed text-sm">{{ $item['fixing'] ?? 'No fixing required.' }}</p>
                                        </div>

                                        {{-- Score --}}
                                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 inline-block min-w-[140px] shadow-sm">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">⭐ Score</span>
                                            </div>
                                            <p class="text-2xl font-black text-gray-900 leading-none">{{ $item['score'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (!empty($evaluationData['action_plan']))
                        <div class="p-4 border border-blue-100 bg-blue-50 rounded-lg">
                            <p class="text-sm font-semibold text-blue-800 uppercase mb-2">Action Plan</p>
                            <p class="text-sm text-blue-900 whitespace-pre-wrap">{{ $evaluationData['action_plan'] }}</p>
                        </div>
                    @endif

                    {{-- Keep old fields as fallback if they exist --}}
                    @if (empty($evaluationData['grading_criteria']) && !empty($evaluationData['code_quality']))
                         <div class="space-y-4">
                            @foreach (['code_quality', 'assignment_logic', 'documentation', 'completion'] as $section)
                                @if (!empty($evaluationData[$section]))
                                    <div>
                                        <p class="text-body-sm font-semibold text-txt-primary uppercase">{{ str_replace('_', ' ', ucfirst($section)) }}</p>
                                        <p class="text-body-sm text-txt-muted">Score: {{ $evaluationData[$section]['score'] ?? 'N/A' }}</p>
                                        <p class="text-body-sm text-txt-primary whitespace-pre-wrap">{{ $evaluationData[$section]['feedback'] ?? 'No feedback provided.' }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Dynamic catch-all for any other fields in the JSON --}}
                    @foreach ($evaluationData as $key => $value)
                        @if (!in_array($key, ['summary', 'grading_criteria', 'action_plan', 'error', 'learner_feedback', 'overall_score', 'code_quality', 'assignment_logic', 'documentation', 'completion', 'areas_for_improvement', 'original_prompt_content', 'assignment_title', 'technology_detected', 'detected_files', 'missing_requirements', 'strengths', 'weaknesses', 'ksa_index']))
                            <div class="mt-6 border-t pt-6">
                                <p class="text-sm font-semibold text-gray-700 uppercase mb-2">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                                @if (is_array($value))
                                    <div class="text-sm text-gray-600">
                                        @if (array_is_list($value))
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach ($value as $item)
                                                    <li>{{ is_array($item) ? json_encode($item) : $item }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <pre class="text-xs overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $value }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    <div class="mt-8 border-t pt-6">
                        <details class="group">
                            <summary class="flex items-center justify-between cursor-pointer list-none p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="text-sm font-semibold text-gray-700 uppercase">View Raw JSON Response</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24"><path d="M6 9l6 6 6-6"></path></svg>
                                </span>
                            </summary>
                            <div class="mt-4 p-4 bg-gray-900 rounded-lg overflow-x-auto">
                                <pre class="text-xs text-green-400 font-mono">{{ json_encode($evaluationData, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </details>
                    </div>
                </div>
@else
                <div class="prose prose-sm max-w-full whitespace-pre-wrap break-words text-body-sm text-txt-primary">{{ $evaluation->evaluation_report }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
