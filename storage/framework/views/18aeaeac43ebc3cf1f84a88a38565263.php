<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'AI Assignment Evaluator','apiStatus' => $apiStatus ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('AI Assignment Evaluator'),'apiStatus' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($apiStatus ?? null)]); ?>
    <div class="page-body max-w-5xl mx-auto flex flex-col gap-8">
        <div class="bg-white/95 backdrop-blur-xl border border-white/20 shadow-[0_8px_30px_rgb(0,0,0,0.04)] w-full p-8 rounded-[32px]">
            <?php if(!isset($latestEvaluation)): ?>
                <div class="mb-8">
                    <h2 class="text-[22px] font-bold text-[#111827] mb-2">Submission Setup</h2>
                    <p class="text-[15px] text-[#4B5563]">Select your module assignment and upload your project submission.</p>
                </div>
                
                <form method="POST" action="<?php echo e(route('ai.evaluator.process')); ?>" enctype="multipart/form-data" 
                  x-data="{ 
                      loading: false, 
                      progress: 0, 
                      statusText: 'Uploading files...',
                      startProgress() {
                          this.loading = true;
                          this.progress = 0;
                          this.statusText = 'Uploading files...';
                          
                          setTimeout(() => { this.statusText = 'Extracting submission zip...'; }, 1000);
                          setTimeout(() => { this.statusText = 'Assembling code context...'; }, 3000);
                          setTimeout(() => { this.statusText = 'AI is analyzing code and generating report (this may take up to a minute)...'; }, 6000);
                          
                          let interval = setInterval(() => {
                              if (this.progress < 40) {
                                  this.progress += 2;
                              } else if (this.progress < 80) {
                                  this.progress += 0.5;
                              } else if (this.progress < 98) {
                                  this.progress += 0.1;
                              }
                          }, 500);
                      }
                  }" 
                  @submit.prevent="startProgress(); $event.target.submit();">
                <?php echo csrf_field(); ?>

                <div class="flex flex-wrap gap-4 mb-6">
                    <!-- The Learner Name and Assignment No are now automatically extracted by AI -->
                    
                    <?php if($errors->any()): ?>
                        <div class="w-full bg-red-50 text-red-500 text-sm p-4 rounded-xl border border-red-100 mb-2">
                            <ul class="list-disc pl-5">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <!-- Prompt File Box -->
                    <div class="bg-[#FDFDFD] border border-[#E5E7EB] rounded-2xl p-4 shadow-sm hover:border-brand-primary/40 transition-colors flex-1 min-w-[200px] flex flex-col" x-data="{ prompts: [] }" @prompts-updated.window="prompts = $event.detail" x-init="fetch('/prompts').then(r => r.json()).then(d => { prompts = d; })">
                        <div>
                            <div class="flex justify-between items-center mb-0.5">
                                <label class="block font-bold text-[13px] text-[#111827]">Select Module and Assignment</label>
                            </div>
                            <p class="text-[11px] text-[#6B7280] mb-3">Choose the assignment prompt you want to evaluate against.</p>
                        </div>
                        
                        <div class="mt-auto">
                            <select name="saved_prompt_id" required class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-shadow">
                                <option value="">-- Select Module and Assignment --</option>
                                <template x-for="p in prompts" :key="p.id">
                                    <option :value="p.id" x-text="p.title"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- ZIP File Box -->
                    <div class="bg-[#FDFDFD] border border-[#E5E7EB] rounded-2xl p-4 shadow-sm hover:border-brand-primary/40 transition-colors flex-1 min-w-[200px] flex flex-col">
                        <div>
                            <div class="flex justify-between items-center mb-0.5">
                                <label class="block font-bold text-[13px] text-[#111827]" for="zip_file">Submission ZIP</label>
                            </div>
                            <p class="text-[11px] text-[#6B7280] mb-3">Upload your project submission (ZIP). Max 100MB.</p>
                        </div>
                        <div class="mt-auto">
                            <input id="zip_file" type="file" name="zip_file" accept=".zip" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" required @change="if($event.target.files[0] && $event.target.files[0].size > 100*1024*1024) { alert('ZIP file must not exceed 100MB.'); $event.target.value = ''; }">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span x-show="!loading">Run grading</span>
                        <span x-show="loading" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>

                <!-- Progress Bar -->
                <div x-show="loading" class="mt-8 bg-surface-panel p-6 rounded-2xl border border-border shadow-sm" style="display: none;" x-transition>
                    <div class="flex justify-between text-sm font-semibold text-txt-primary mb-2">
                        <span x-text="statusText"></span>
                        <span x-text="Math.round(progress) + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-brand-primary h-3 rounded-full transition-all duration-500 ease-out" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
            </form>
            <?php endif; ?>

            <?php if(isset($latestEvaluation)): ?>
                <div class="mt-8 border-t border-gray-100 pt-8" id="latest-evaluation-result">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-[20px] font-bold text-[#111827]">Latest Evaluation Result</h2>
                        <div class="flex gap-2">
                            <a href="<?php echo e(route('ai.evaluator')); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-1.5 px-4 rounded-lg text-sm transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Go back to AI AutoGrader
                            </a>
                            <a href="<?php echo e(route('ai.evaluator.download.pdf', $latestEvaluation)); ?>" class="btn-secondary text-sm py-1 px-3" download>Download (.txt)</a>
                            <a href="<?php echo e(route('ai.evaluator.download.docx', $latestEvaluation)); ?>" class="btn-secondary text-sm py-1 px-3" download>Download (.docx)</a>
                        </div>
                    </div>
                    <?php echo $__env->make('ai-evaluator._report', ['evaluation' => $latestEvaluation, 'evaluationData' => $evaluationData, 'isJson' => $isJson], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endif; ?>


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
<?php /**PATH C:\xampp\htdocs\AiAutoGrader - Final\resources\views/ai-evaluator/index.blade.php ENDPATH**/ ?>