<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Admin: Manage Prompts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Admin: Manage Prompts')]); ?>
    <div class="page-body max-w-5xl mx-auto flex flex-col gap-8">
        <div class="bg-white/95 backdrop-blur-xl border border-white/20 shadow-[0_8px_30px_rgb(0,0,0,0.04)] w-full p-8 rounded-[32px]">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-[22px] font-bold text-[#111827] mb-2">Prompt Management</h2>
                    <p class="text-[15px] text-[#4B5563]">Upload or generate grading prompts for the AI Evaluator.</p>
                </div>
                <div class="flex gap-4">
                    <a href="<?php echo e(route('prompt.generator')); ?>" class="px-5 py-2.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-semibold rounded-xl text-[14px] transition-all">
                        Open Prompt Generator
                    </a>
                </div>
            </div>


            <!-- Upload Custom Prompt -->
            <div class="bg-[#FDFDFD] border border-[#E5E7EB] rounded-2xl p-6 shadow-sm mb-8">
                <h3 class="font-bold text-[16px] text-[#111827] mb-4">Upload Custom Prompt File</h3>
                <form action="<?php echo e(route('admin.prompts.upload')); ?>" method="POST" enctype="multipart/form-data" class="flex gap-4 items-end">
                    <?php echo csrf_field(); ?>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prompt Title</label>
                        <input type="text" name="prompt_title" required class="w-full px-4 py-2 border border-gray-300 rounded-xl" placeholder="e.g. Data Science Assignment 1">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Module Name (Optional)</label>
                        <input type="text" name="module_name" class="w-full px-4 py-2 border border-gray-300 rounded-xl" placeholder="e.g. Front End Development">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prompt File (PDF/TXT/DOCX)</label>
                        <input type="file" name="prompt_file" accept=".txt,.pdf,.docx,.doc" required class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-white file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition-all">
                        Upload
                    </button>
                </form>
            </div>

            <!-- List of Saved Prompts -->
            <div>
                <h3 class="font-bold text-[16px] text-[#111827] mb-4">Saved Prompts</h3>
                <div x-data="{ activeModule: null }" class="flex flex-col gap-4">
                    <?php $__empty_1 = true; $__currentLoopData = $promptsByModule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $moduleName => $prompts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                            <!-- Accordion Header -->
                            <button @click="activeModule = activeModule === '<?php echo e($moduleName); ?>' ? null : '<?php echo e($moduleName); ?>'" class="w-full px-6 py-4 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{'rotate-180': activeModule === '<?php echo e($moduleName); ?>'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <h3 class="font-bold text-gray-900"><?php echo e($moduleName); ?></h3>
                                </div>
                                <span class="text-sm font-semibold text-gray-500 bg-gray-200 px-3 py-1 rounded-full"><?php echo e($prompts->count()); ?></span>
                            </button>
                            
                            <!-- Accordion Body -->
                            <div x-show="activeModule === '<?php echo e($moduleName); ?>'" x-transition class="border-t border-gray-200 p-4 flex flex-col gap-3 bg-white">
                                <?php $__currentLoopData = $prompts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prompt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="border border-gray-100 rounded-lg p-4 flex items-center justify-between bg-gray-50">
                                        <div>
                                            <h4 class="font-bold text-gray-900"><?php echo e($prompt->title); ?></h4>
                                            <p class="text-xs text-gray-500 mt-1">Uploaded: <?php echo e($prompt->created_at->format('M d, Y')); ?></p>
                                        </div>
                                        <div class="flex gap-4 items-center">
                                            <button onclick="alert(`<?php echo e(addslashes(substr($prompt->content, 0, 500))); ?>...`)" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Preview</button>
                                            <form action="<?php echo e(route('admin.prompts.destroy', $prompt)); ?>" method="POST" onsubmit="return confirm('Are you sure?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-sm font-semibold text-red-500 hover:text-red-700 transition-colors">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-span-full text-center text-gray-500 py-8">
                            No saved prompts found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

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
<?php /**PATH C:\xampp\htdocs\AiAutoGrader - Final\resources\views/admin/prompts/index.blade.php ENDPATH**/ ?>