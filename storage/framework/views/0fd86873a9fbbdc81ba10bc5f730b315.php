<?php if (isset($component)) { $__componentOriginal6107cafe1a6b2bb3ae2fbdc60a313162 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6107cafe1a6b2bb3ae2fbdc60a313162 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.auth','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.auth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="auth-shell">

        
        <section class="auth-panel--brand">
            <div class="max-w-md text-center">

                <div class="w-20 h-20 bg-brand-accent rounded-xl flex items-center justify-center mx-auto mb-8">
                    <span class="font-heading font-black text-display text-txt-inverse">
                        AI
                    </span>
                </div>

                <h1 class="font-heading font-black text-display uppercase tracking-wide mb-4">
                    AI AUTO GRADER
                </h1>

                <p class="font-base text-body-lg text-txt-inverse/80 leading-relaxed">
                    Evaluate and grade assignments automatically
                    with the precision and consistency
                    of advanced AI.
                </p>

                <div class="mt-12 grid grid-cols-3 gap-6 text-center">

                    <div>
                        <div class="text-title-lg font-heading font-bold text-brand-accent">
                            Stories
                        </div>

                        <div class="text-body-sm text-txt-inverse/60 mt-1">
                            AI-Generated
                        </div>
                    </div>

                    <div>
                        <div class="text-title-lg font-heading font-bold text-brand-accent">
                            Features
                        </div>

                        <div class="text-body-sm text-txt-inverse/60 mt-1">
                            Multi-Format
                        </div>
                    </div>

                    <div>
                        <div class="text-title-lg font-heading font-bold text-brand-accent">
                            Prompts
                        </div>

                        <div class="text-body-sm text-txt-inverse/60 mt-1">
                            Micro-Optimized
                        </div>
                    </div>

                </div>
            </div>
        </section>

        
        <section class="auth-panel--form">

            <div class="w-full max-w-md">

                <div class="card">

                    <h2 class="font-heading font-bold text-title text-txt-primary mb-2">
                        Welcome Back
                    </h2>

                    <p class="text-body-sm text-txt-muted mb-8">
                        Login to continue to AI Auto Grader
                    </p>

                    <?php if(session('error')): ?>
                        <div class="alert-danger mb-6">
                            <?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4">

                        <?php echo csrf_field(); ?>

                        <div>
                            <label class="block mb-2 font-medium">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="admin@test.com"
                                class="w-full border rounded-lg px-4 py-3"
                                required
                            >
                        </div>

                        <div>
                            <label class="block mb-2 font-medium">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                value="12345678"
                                class="w-full border rounded-lg px-4 py-3"
                                required
                            >
                        </div>

                        <button
                            type="submit"
                            class="w-full py-3 bg-black text-white rounded-lg font-semibold"
                        >
                            Login
                        </button>

                    </form>

                </div>

                <p class="text-center text-caption text-txt-muted mt-6">
                    &copy; <?php echo e(date('Y')); ?> CLaaS2SaaS. All rights reserved.
                </p>

            </div>

        </section>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6107cafe1a6b2bb3ae2fbdc60a313162)): ?>
<?php $attributes = $__attributesOriginal6107cafe1a6b2bb3ae2fbdc60a313162; ?>
<?php unset($__attributesOriginal6107cafe1a6b2bb3ae2fbdc60a313162); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6107cafe1a6b2bb3ae2fbdc60a313162)): ?>
<?php $component = $__componentOriginal6107cafe1a6b2bb3ae2fbdc60a313162; ?>
<?php unset($__componentOriginal6107cafe1a6b2bb3ae2fbdc60a313162); ?>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\storyforge\storyforge\resources\views/auth/login.blade.php ENDPATH**/ ?>