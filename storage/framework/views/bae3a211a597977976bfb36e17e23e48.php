<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Dashboard — AI Agent - Auto Grader']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Dashboard — AI Agent - Auto Grader')]); ?>
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">Dashboard</h1>
            <p class="text-body-sm text-txt-muted mt-1">Welcome back, <?php echo e(Auth::user()->name); ?></p>
        </div>
    </div>

    <div class="page-body">
        
        <div class="mb-6">
            <div class="card p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-caption text-txt-muted uppercase tracking-wider font-semibold">API Connection</p>
                        <p class="text-body-sm mt-2 text-txt-primary"><?php echo e($apiStatus['message']); ?></p>
                    </div>
                    <span class="badge-<?php echo e($apiStatus['connected'] ? 'success' : 'danger'); ?>">
                        <?php echo e($apiStatus['connected'] ? 'Connected' : 'Not Connected'); ?>

                    </span>
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
<?php /**PATH C:\xampp\htdocs\storyforge\storyforge\resources\views/dashboard/index.blade.php ENDPATH**/ ?>