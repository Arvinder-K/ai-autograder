<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'AI Auto Grader'); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <script>
        window.AppConfig = {
            baseUrl: <?php echo json_encode(rtrim(config('app.url'), '/'), 512) ?>,
        };
    </script>
</head>

<body class="antialiased" style="background: linear-gradient(135deg, #FFF0E6 0%, #F0FDF4 50%, #E6F8F6 100%); background-attachment: fixed; min-height: 100vh; color: #1E293B;">
    <div class="app-shell" x-data="{ sidebarOpen: false }">
        
        <header class="fixed top-0 left-0 right-0 z-40 flex items-center px-6 border-b shadow-sm" style="height: 4.5rem; background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); border-color: rgba(226,232,240,0.8);">
            <div class="flex items-center gap-4 flex-1">

                
                <a href="<?php echo e(url('/')); ?>"
                    class="flex items-center gap-3 group mr-8">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center font-heading font-black text-sm transition-all" style="background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.3); color: #6366F1;">
                        AI</div>
                    <span class="font-heading font-bold tracking-tight hidden sm:inline" style="color: #111827; font-size: 1.25rem;">Auto Grader</span>
                </a>
            </div>

            
            <?php if(isset($apiStatus) && $apiStatus): ?>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border shadow-sm" style="border-color: rgba(226,232,240,0.8); background: rgba(248,250,252,0.8);" title="<?php echo e($apiStatus['message'] ?? ''); ?>">
                    <div class="w-2 h-2 rounded-full <?php echo e((isset($apiStatus['connected']) && $apiStatus['connected']) ? 'bg-emerald-500' : 'bg-red-500'); ?>"></div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider" style="color: #475569;"><?php echo e((isset($apiStatus['connected']) && $apiStatus['connected']) ? 'API Connected' : 'API Offline'); ?></span>
                </div>
            <?php endif; ?>

            
            <div>
                <a href="<?php echo e(route('admin.prompts.index')); ?>" class="flex items-center gap-2 px-3 py-1.5 rounded-full border shadow-sm transition-colors hover:bg-slate-50" style="border-color: rgba(226,232,240,0.8); background: rgba(255,255,255,0.8);">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="text-[12px] font-semibold text-slate-700">Admin: Manage Prompts</span>
                </a>
            </div>

            
            <div>
                <a href="<?php echo e(route('admin.analytics')); ?>" class="flex items-center gap-2 px-3 py-1.5 rounded-full border shadow-sm transition-colors hover:bg-slate-50" style="border-color: rgba(226,232,240,0.8); background: rgba(255,255,255,0.8);">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="text-[12px] font-semibold text-slate-700">Admin: Analytics</span>
                </a>
            </div>

            
            <div x-data="historyManager()">
                <button @click="openModal()" class="flex items-center gap-2 px-3 py-1.5 rounded-full border shadow-sm transition-colors hover:bg-slate-50" style="border-color: rgba(226,232,240,0.8); background: rgba(255,255,255,0.8);">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-[12px] font-semibold text-slate-700">History</span>
                </button>

                <!-- Alpine Modal -->
                <template x-teleport="body">
                    <div x-show="isOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="isOpen" @click="closeModal()" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                            <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                                
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                            <div class="flex justify-between items-center mb-4">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Grading History</h3>
                                                <button @click="closeModal()" class="text-gray-400 hover:text-gray-500">
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </div>
                                            
                                            <!-- List View -->
                                            <div class="overflow-hidden border border-gray-200 sm:rounded-md mt-4 max-h-[60vh] overflow-y-auto">
                                                <ul role="list" class="divide-y divide-gray-200">
                                                    <template x-for="eval in evaluations" :key="eval.id">
                                                        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 flex items-center justify-between gap-4">
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-medium text-indigo-600 truncate" x-text="eval.assignment_name"></p>
                                                                <p class="mt-1 flex items-center text-xs text-gray-500" x-text="'Learner: ' + eval.student_name"></p>
                                                            </div>
                                                            <div class="flex flex-col items-end gap-1">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="eval.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" x-text="eval.status"></span>
                                                                <p class="text-xs text-gray-400" x-text="new Date(eval.created_at).toLocaleDateString()"></p>
                                                            </div>
                                                            <div class="flex gap-2 ml-4">
                                                                <a :href="'/ai-evaluator/' + eval.id" class="text-xs text-blue-600 hover:text-blue-900 bg-blue-50 px-2 py-1 rounded">View</a>
                                                                <button @click="deleteEval(eval.id)" class="text-xs text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded">Delete</button>
                                                            </div>
                                                        </li>
                                                    </template>
                                                    <li x-show="evaluations.length === 0" class="px-4 py-4 text-sm text-gray-500 text-center">No previous results found.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('historyManager', () => ({
                        isOpen: false,
                        evaluations: [],
                        openModal() {
                            this.isOpen = true;
                            this.fetchEvaluations();
                        },
                        closeModal() {
                            this.isOpen = false;
                        },
                        async fetchEvaluations() {
                            try {
                                const response = await fetch('/evaluations');
                                this.evaluations = await response.json();
                            } catch (error) {
                                console.error('Error fetching evaluations:', error);
                            }
                        },
                        async deleteEval(id) {
                            if (!confirm('Are you sure you want to delete this evaluation?')) return;
                            
                            try {
                                const response = await fetch(`/evaluations/${id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                
                                if (response.ok) {
                                    await this.fetchEvaluations();
                                }
                            } catch (error) {
                                console.error('Error deleting evaluation:', error);
                            }
                        }
                    }));
                });
            </script>

        </header>

        
        <main class="page-shell">
            
            <div class="px-8 mt-6 space-y-4">
                <?php if(session('success')): ?>
                    <div class="alert-success" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)">
                        <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium flex-1"><?php echo e(session('success')); ?></p>
                        <button @click="show = false" class="text-txt-muted hover:text-white transition-colors">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert-danger" x-data="{ show: true }" x-show="show" x-transition>
                        <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium flex-1"><?php echo e(session('error')); ?></p>
                        <button @click="show = false" class="text-txt-muted hover:text-white transition-colors">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="alert-danger">
                        <ul class="list-disc list-inside text-sm font-medium">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <?php echo e($slot); ?>

        </main>
    </div>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\AiAutoGrader - Final\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>