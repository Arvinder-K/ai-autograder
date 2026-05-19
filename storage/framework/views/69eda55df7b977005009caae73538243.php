<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'AI Evaluation Result']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('AI Evaluation Result')]); ?>
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">AI Evaluation Result</h1>
            <p class="text-body-sm text-txt-muted mt-1">Review the evaluation and download the report as PDF or DOCX.</p>
        </div>

        <?php if (! (isset($forPdf) && $forPdf)): ?>
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?php echo e(route('ai.evaluator.download.pdf', $evaluation)); ?>" class="btn-secondary" download>Download Report (.txt)</a>
                <a href="<?php echo e(route('ai.evaluator.download.docx', $evaluation)); ?>" class="btn-secondary" download>Download Report (.docx)</a>
                <a href="<?php echo e(route('ai.evaluator')); ?>" class="btn-ghost">Back to Evaluator</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="page-body grid gap-6">
        <div class="card">
            <div class="card-header">Assignment Details</div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Student Name</p>
                    <p class="font-semibold text-body text-txt-primary"><?php echo e($evaluation->student_name); ?></p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Assignment Title</p>
                    <p class="font-semibold text-body text-txt-primary"><?php echo e($evaluationData['assignment_title'] ?? $evaluation->assignment_name); ?></p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Status</p>
                    <p class="font-semibold text-body text-txt-primary"><?php echo e(ucfirst($evaluation->status)); ?></p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Created</p>
                    <p class="font-semibold text-body text-txt-primary"><?php echo e($evaluation->created_at->format('M d, Y H:i')); ?></p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Prompt File</p>
                    <p class="font-semibold text-body text-txt-primary truncate" title="<?php echo e(basename($evaluation->prompt_file)); ?>"><?php echo e(basename($evaluation->prompt_file)); ?></p>
                </div>
                <div>
                    <p class="text-caption text-txt-muted uppercase tracking-wide">Submission File</p>
                    <p class="font-semibold text-body text-txt-primary truncate" title="<?php echo e(basename($evaluation->zip_file)); ?>"><?php echo e(basename($evaluation->zip_file)); ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">AI Evaluation Report</div>
            <?php if(!empty($isJson) && is_array($evaluationData)): ?>
                <div class="space-y-6">
                    <?php if(isset($evaluationData['error'])): ?>
                        <div class="p-4 border border-red-100 bg-red-50 rounded-lg">
                            <p class="text-sm font-semibold text-red-800 uppercase mb-2">AI Service Error</p>
                            <p class="text-sm text-red-900"><?php echo e($evaluationData['learner_feedback'] ?? 'An error occurred.'); ?></p>
                            <p class="text-xs text-red-700 mt-2">Technical details: <?php echo e($evaluationData['error']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($evaluationData['summary'])): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-caption text-txt-muted uppercase tracking-wide">⭐ Score</p>
                                <p class="text-2xl font-bold text-txt-primary"><?php echo e($evaluationData['summary']['earned_score'] ?? '0'); ?> / <?php echo e($evaluationData['summary']['max_score'] ?? '100'); ?></p>
                            </div>
                            <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-lg">
                                <p class="text-caption text-indigo-800 font-bold uppercase tracking-wide">KSA Index</p>
                                <p class="text-2xl font-bold text-indigo-900"><?php echo e($evaluationData['summary']['ksa_index'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($evaluationData['grading_criteria'])): ?>
                        <div class="space-y-6">
                            <?php $__currentLoopData = $evaluationData['grading_criteria']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo e($item['criteria']); ?></h3>
                                    
                                    <div class="space-y-5">
                                        
                                        <div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                                <span class="font-medium text-gray-900">Feedback</span>
                                            </div>
                                            <p class="text-gray-700 leading-relaxed text-sm"><?php echo e($item['feedback']); ?></p>
                                        </div>

                                        
                                        <div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                                </svg>
                                                <span class="font-medium text-gray-900">Suggestion</span>
                                            </div>
                                            <p class="text-gray-700 leading-relaxed text-sm"><?php echo e($item['fixing'] ?? 'No fixing required.'); ?></p>
                                        </div>

                                        
                                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 inline-block min-w-[140px] shadow-sm">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">⭐ Score</span>
                                            </div>
                                            <p class="text-2xl font-black text-gray-900 leading-none"><?php echo e($item['score']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($evaluationData['action_plan'])): ?>
                        <div class="p-4 border border-blue-100 bg-blue-50 rounded-lg">
                            <p class="text-sm font-semibold text-blue-800 uppercase mb-2">Action Plan</p>
                            <p class="text-sm text-blue-900 whitespace-pre-wrap"><?php echo e($evaluationData['action_plan']); ?></p>
                        </div>
                    <?php endif; ?>

                    
                    <?php if(empty($evaluationData['grading_criteria']) && !empty($evaluationData['code_quality'])): ?>
                         <div class="space-y-4">
                            <?php $__currentLoopData = ['code_quality', 'assignment_logic', 'documentation', 'completion']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(!empty($evaluationData[$section])): ?>
                                    <div>
                                        <p class="text-body-sm font-semibold text-txt-primary uppercase"><?php echo e(str_replace('_', ' ', ucfirst($section))); ?></p>
                                        <p class="text-body-sm text-txt-muted">Score: <?php echo e($evaluationData[$section]['score'] ?? 'N/A'); ?></p>
                                        <p class="text-body-sm text-txt-primary whitespace-pre-wrap"><?php echo e($evaluationData[$section]['feedback'] ?? 'No feedback provided.'); ?></p>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    
                    <?php $__currentLoopData = $evaluationData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(!in_array($key, ['summary', 'grading_criteria', 'action_plan', 'error', 'learner_feedback', 'overall_score', 'code_quality', 'assignment_logic', 'documentation', 'completion', 'areas_for_improvement', 'original_prompt_content', 'assignment_title', 'technology_detected', 'detected_files', 'missing_requirements', 'strengths', 'weaknesses', 'ksa_index'])): ?>
                            <div class="mt-6 border-t pt-6">
                                <p class="text-sm font-semibold text-gray-700 uppercase mb-2"><?php echo e(str_replace('_', ' ', ucfirst($key))); ?></p>
                                <?php if(is_array($value)): ?>
                                    <div class="text-sm text-gray-600">
                                        <?php if(array_is_list($value)): ?>
                                            <ul class="list-disc list-inside space-y-1">
                                                <?php $__currentLoopData = $value; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><?php echo e(is_array($item) ? json_encode($item) : $item); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        <?php else: ?>
                                            <pre class="text-xs overflow-x-auto"><?php echo e(json_encode($value, JSON_PRETTY_PRINT)); ?></pre>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-sm text-gray-800 whitespace-pre-wrap"><?php echo e($value); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="mt-8 border-t pt-6">
                        <details class="group">
                            <summary class="flex items-center justify-between cursor-pointer list-none p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="text-sm font-semibold text-gray-700 uppercase">View Raw JSON Response</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24"><path d="M6 9l6 6 6-6"></path></svg>
                                </span>
                            </summary>
                            <div class="mt-4 p-4 bg-gray-900 rounded-lg overflow-x-auto">
                                <pre class="text-xs text-green-400 font-mono"><?php echo e(json_encode($evaluationData, JSON_PRETTY_PRINT)); ?></pre>
                            </div>
                        </details>
                    </div>
                </div>
<?php else: ?>
                <div class="prose prose-sm max-w-full whitespace-pre-wrap break-words text-body-sm text-txt-primary"><?php echo e($evaluation->evaluation_report); ?></div>
            <?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\storyforge\storyforge\resources\views/ai-evaluator/result.blade.php ENDPATH**/ ?>