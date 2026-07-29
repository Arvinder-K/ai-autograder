        <div class="card">
            <div class="card-header">Assignment Details</div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Learner Name</p>
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
                        <div class="p-4 border border-red-500/30 bg-status-danger-bg rounded-lg">
                            <p class="text-sm font-semibold text-status-danger-text uppercase mb-2">AI Service Error</p>
                            <p class="text-sm text-status-danger-text">{{ $evaluationData['learner_feedback'] ?? 'An error occurred.' }}</p>
                            <p class="text-xs text-red-400 mt-2">Technical details: {{ $evaluationData['error'] }}</p>
                        </div>
                    @endif

                    @php
                        $criteriaList = [];
                        if (!empty($evaluationData['grading_criteria'])) {
                            $criteriaList = $evaluationData['grading_criteria'];
                        } elseif (!empty($evaluationData['criteria_feedback'])) {
                            foreach ($evaluationData['criteria_feedback'] as $key => $val) {
                                $val['criteria'] = ucwords(str_replace('_', ' ', $key));
                                $criteriaList[] = $val;
                            }
                        }

                        // Fallback summary computation if JSON was truncated before the summary block
                        if (empty($evaluationData['summary']) && !empty($criteriaList)) {
                            $calculatedEarned = 0;
                            $calculatedMax = 0;
                            foreach ($criteriaList as $item) {
                                $scoreStr = $item['score'] ?? '';
                                if (preg_match('/(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)/', $scoreStr, $matches)) {
                                    $calculatedEarned += (float)$matches[1];
                                    $calculatedMax += (float)$matches[2];
                                }
                            }
                            if ($calculatedMax > 0) {
                                $evaluationData['summary'] = [
                                    'earned_score' => $calculatedEarned,
                                    'max_score' => $calculatedMax,
                                    'ksa_index' => 'Partial Data'
                                ];
                            }
                        }
                    @endphp

                    @if (!empty($evaluationData['summary']))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-surface-panel-muted border border-border rounded-lg shadow-sm">
                                <p class="text-caption text-txt-muted uppercase tracking-wide">⭐ Score</p>
                                <p class="text-2xl font-bold text-txt-primary">{{ $evaluationData['summary']['earned_score'] ?? '0' }} / {{ $evaluationData['summary']['max_score'] ?? '100' }}</p>
                            </div>
                            <div class="p-4 bg-brand-primary/10 border border-brand-primary/30 rounded-lg shadow-[0_0_15px_rgba(99,102,241,0.1)]">
                                <p class="text-caption text-brand-primary font-bold uppercase tracking-wide">KSA Index</p>
                                <p class="text-2xl font-bold text-txt-primary">{{ $evaluationData['summary']['ksa_index'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @endif
                    
                    @if (!empty($criteriaList))
                        <div class="space-y-6">
                            @foreach ($criteriaList as $item)
                                <div class="bg-surface-panel-muted/50 border border-border rounded-lg p-6 shadow-sm">
                                    <h3 class="text-lg font-medium text-txt-primary mb-4">{{ $item['criteria'] ?? 'Criteria' }}</h3>
                                    
                                    <div class="space-y-5">
                                        {{-- Feedback --}}
                                        <div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                                <span class="font-medium text-txt-primary">Feedback</span>
                                            </div>
                                            <p class="text-txt-secondary leading-relaxed text-sm">{{ $item['feedback'] ?? 'No feedback provided.' }}</p>
                                        </div>

                                        {{-- Suggestion --}}
                                        <div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                                </svg>
                                                <span class="font-medium text-txt-primary">Suggestion</span>
                                            </div>
    <p class="text-txt-secondary leading-relaxed text-sm">
        @php
            $suggestionText = !empty($item['fixing']) ? $item['fixing'] : (!empty($item['suggestion']) ? $item['suggestion'] : '');
            if (empty(trim($suggestionText))) {
                // If the score string has numbers, try to figure out if it's less than max
                $scoreStr = $item['score'] ?? '';
                if (preg_match('/(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)/', $scoreStr, $matches)) {
                    if ((float)$matches[1] < (float)$matches[2]) {
                        $suggestionText = 'Points were deducted, but the AI did not provide a specific fixing suggestion. Please refer to the feedback above.';
                    } else {
                        $suggestionText = 'No fixing required.';
                    }
                } else {
                    $suggestionText = 'No fixing required.';
                }
            }
        @endphp
        {{ $suggestionText }}
    </p>
                                        </div>

                                        {{-- Score --}}
                                        <div class="bg-surface-panel p-4 rounded-lg border border-border inline-block min-w-[140px] shadow-sm">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-bold text-txt-muted uppercase tracking-widest">⭐ Score</span>
                                            </div>
                                            <p class="text-2xl font-black text-txt-primary leading-none">{{ $item['score'] ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (!empty($evaluationData['action_plan']))
                        <div class="p-4 border border-blue-500/30 bg-status-info-bg rounded-lg">
                            <p class="text-sm font-semibold text-status-info-text uppercase mb-2">Action Plan</p>
                            <p class="text-sm text-txt-primary whitespace-pre-wrap">{{ $evaluationData['action_plan'] }}</p>
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
                        @if (!in_array($key, ['summary', 'grading_criteria', 'criteria_feedback', 'action_plan', 'error', 'learner_feedback', 'overall_score', 'code_quality', 'assignment_logic', 'documentation', 'completion', 'areas_for_improvement', 'original_prompt_content', 'assignment_title', 'technology_detected', 'detected_files', 'missing_requirements', 'strengths', 'weaknesses', 'ksa_index', 'student_name', 'assignment_name', 'learner_name', 'learner_email']))
                            @if(empty($value) && !is_numeric($value))
                                @continue
                            @endif
                            <div class="mt-6 border-t border-border/50 pt-6">
                                <p class="text-sm font-semibold text-txt-secondary uppercase mb-2">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                                @if (is_array($value))
                                    <div class="text-sm text-txt-muted">
                                        @if (array_is_list($value))
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach ($value as $item)
                                                    <li>{{ is_array($item) ? json_encode($item) : $item }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <pre class="text-xs overflow-x-auto text-txt-primary">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-txt-primary whitespace-pre-wrap">{{ $value }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    <div class="mt-8 border-t border-border/50 pt-6">
                        <details class="group">
                            <summary class="flex items-center justify-between cursor-pointer list-none p-3 bg-surface-panel-muted rounded-lg hover:bg-surface-panel transition-colors border border-border">
                                <span class="text-sm font-semibold text-txt-secondary uppercase">View Raw JSON Response</span>
                                <span class="transition group-open:rotate-180 text-txt-muted">
                                    <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24"><path d="M6 9l6 6 6-6"></path></svg>
                                </span>
                            </summary>
                            <div class="mt-4 p-4 bg-black/40 border border-border/50 rounded-lg overflow-x-auto">
                                <pre class="text-xs text-brand-accent font-mono">{{ json_encode($evaluationData, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </details>
                    </div>
                </div>
@else
                <div class="prose prose-sm max-w-full whitespace-pre-wrap break-words text-body-sm text-txt-primary">{{ $evaluation->evaluation_report }}</div>
            @endif
        </div>
