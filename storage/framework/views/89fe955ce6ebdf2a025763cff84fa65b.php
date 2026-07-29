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
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <a href="<?php echo e(route('ai.evaluator.download.pdf', $evaluation)); ?>" class="btn-secondary" download>Download Report (.txt)</a>
                <a href="<?php echo e(route('ai.evaluator.download.docx', $evaluation)); ?>" class="btn-secondary" download>Download Report (.docx)</a>
                <a href="<?php echo e(route('ai.evaluator')); ?>" class="btn-ghost">Back to Evaluator</a>
            </div>

        <?php endif; ?>
    </div>

    <div class="page-body grid gap-6">
    <div class="page-body grid gap-6">
        <?php echo $__env->make('ai-evaluator._report', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\AiAutoGrader - Final\resources\views/ai-evaluator/result.blade.php ENDPATH**/ ?>