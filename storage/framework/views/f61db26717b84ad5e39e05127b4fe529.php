<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Admin: Learner Progress Analytics']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Admin: Learner Progress Analytics')]); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="page-body max-w-5xl mx-auto flex flex-col gap-8">
        <div class="bg-white/95 backdrop-blur-xl border border-white/20 shadow-[0_8px_30px_rgb(0,0,0,0.04)] w-full p-8 rounded-[32px]">
            <div class="mb-8">
                <h2 class="text-[22px] font-bold text-[#111827] mb-2">Learner Progress Analytics</h2>
                <p class="text-[15px] text-[#4B5563]">Track the total grades of learners across different modules.</p>
            </div>

            <?php if(empty($chartData)): ?>
                <div class="text-center text-gray-500 py-12 bg-gray-50 rounded-2xl border border-gray-100">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <p class="font-medium">No evaluation data available yet to display analytics.</p>
                </div>
            <?php else: ?>
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl p-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-indigo-600 mb-1">Total Evaluations</p>
                            <h3 class="text-3xl font-black text-indigo-900" id="totalEvalsText"><?php echo e($totalEvaluations); ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl p-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-emerald-600 mb-1" id="avgScoreLabel">Global Average Score</p>
                            <h3 class="text-3xl font-black text-emerald-900" id="avgScoreText"><?php echo e($averageScore); ?>%</h3>
                        </div>
                        <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Learner Progress Chart (Main) -->
                    <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-gray-800">Learner Performance Breakdown</h3>
                            <select id="learnerSelect" class="border border-gray-200 rounded-xl px-4 py-2 w-48 text-sm focus:ring-2 focus:ring-indigo-500 outline-none shadow-sm font-medium bg-gray-50">
                                <option value="All">All Learners</option>
                            </select>
                        </div>
                        <div class="w-full h-[350px]">
                            <canvas id="progressChart"></canvas>
                        </div>
                    </div>

                    <!-- Module Averages Chart (Sidebar) -->
                    <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm flex flex-col">
                        <h3 class="font-bold text-gray-800 mb-6">Module Averages</h3>
                        <div class="w-full h-[300px] flex-1 flex items-center justify-center">
                            <canvas id="moduleChart"></canvas>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const rawData = <?php echo json_encode($chartData, 15, 512) ?>;
                        const learnerNames = [...new Set(rawData.map(item => item.student_name))].filter(Boolean);
                        
                        const select = document.getElementById('learnerSelect');
                        learnerNames.forEach(name => {
                            const option = document.createElement('option');
                            option.value = name;
                            option.textContent = name;
                            select.appendChild(option);
                        });

                        const ctx = document.getElementById('progressChart').getContext('2d');
                        let progressChart = null;
                        
                        const mCtx = document.getElementById('moduleChart').getContext('2d');
                        let moduleChart = null;

                        // Vibrant color palette
                        const colors = [
                            { bg: 'rgba(99, 102, 241, 0.8)', border: 'rgba(79, 70, 229, 1)' },   // Indigo
                            { bg: 'rgba(236, 72, 153, 0.8)', border: 'rgba(219, 39, 119, 1)' },  // Pink
                            { bg: 'rgba(16, 185, 129, 0.8)', border: 'rgba(5, 150, 105, 1)' },   // Emerald
                            { bg: 'rgba(245, 158, 11, 0.8)', border: 'rgba(217, 119, 6, 1)' },   // Amber
                            { bg: 'rgba(139, 92, 246, 0.8)', border: 'rgba(109, 40, 217, 1)' },  // Violet
                            { bg: 'rgba(14, 165, 233, 0.8)', border: 'rgba(2, 132, 199, 1)' },   // Light Blue
                            { bg: 'rgba(244, 63, 94, 0.8)', border: 'rgba(225, 29, 72, 1)' }     // Rose
                        ];

                        function renderChart(filterName) {
                            let filteredData = [];
                            if (filterName === 'All') {
                                filteredData = rawData;
                            } else {
                                filteredData = rawData.filter(item => item.student_name === filterName);
                            }

                            // Update Summary Cards
                            document.getElementById('totalEvalsText').textContent = filteredData.length;
                            
                            if (filteredData.length > 0) {
                                const totalScore = filteredData.reduce((acc, curr) => acc + curr.score, 0);
                                document.getElementById('avgScoreText').textContent = (totalScore / filteredData.length).toFixed(1) + '%';
                            } else {
                                document.getElementById('avgScoreText').textContent = '0%';
                            }

                            if (filterName === 'All') {
                                document.getElementById('avgScoreLabel').textContent = 'Global Average Score';
                            } else {
                                document.getElementById('avgScoreLabel').textContent = filterName + "'s Average Score";
                            }

                            // Use the prompt title directly from the saved_prompt table
                            const labels = filteredData.map(item => item.full_prompt);
                            const scores = filteredData.map(item => item.score);
                            
                            const bgColors = filteredData.map((_, i) => colors[i % colors.length].bg);
                            const borderColors = filteredData.map((_, i) => colors[i % colors.length].border);

                            if (progressChart) {
                                progressChart.destroy();
                            }

                            progressChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Total Grade',
                                        data: scores,
                                        backgroundColor: bgColors,
                                        borderColor: borderColors,
                                        borderWidth: 1,
                                        borderRadius: 4,
                                        barPercentage: 0.6
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            max: 100,
                                            title: {
                                                display: true,
                                                text: 'Total Grade (%)',
                                                font: { weight: 'bold' }
                                            }
                                        },
                                        x: {
                                            ticks: {
                                                autoSkip: true,
                                                maxRotation: 45,
                                                minRotation: 0,
                                                font: { size: 11 }
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                title: function(context) {
                                                    const idx = context[0].dataIndex;
                                                    return filteredData[idx].full_prompt + (filterName === 'All' ? ' (' + filteredData[idx].student_name + ')' : '');
                                                },
                                                label: function(context) {
                                                    return 'Total Grade: ' + context.parsed.y + '%';
                                                }
                                            }
                                        }
                                    }
                                }
                            });

                            // Calculate and Render Module Averages Chart dynamically
                            const moduleStats = {};
                            filteredData.forEach(item => {
                                const mod = item.module_name;
                                if (!moduleStats[mod]) {
                                    moduleStats[mod] = { total: 0, count: 0 };
                                }
                                moduleStats[mod].total += item.score;
                                moduleStats[mod].count++;
                            });

                            const mLabels = Object.keys(moduleStats);
                            const mScores = mLabels.map(mod => (moduleStats[mod].total / moduleStats[mod].count).toFixed(1));
                            
                            const mBg = mLabels.map((_, i) => colors[i % colors.length].bg.replace('0.8', '0.6'));
                            const mBorder = mLabels.map((_, i) => colors[i % colors.length].border);

                            if (moduleChart) {
                                moduleChart.destroy();
                            }

                            moduleChart = new Chart(mCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: mLabels,
                                    datasets: [{
                                        data: mScores,
                                        backgroundColor: mBg,
                                        borderColor: mBorder,
                                        borderWidth: 2,
                                        hoverOffset: 4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                            labels: {
                                                usePointStyle: true,
                                                padding: 20,
                                                font: { size: 11 }
                                            }
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return ' Avg Score: ' + context.parsed + '%';
                                                }
                                            }
                                        }
                                    },
                                    cutout: '65%'
                                }
                            });
                        }

                        renderChart('All');

                        select.addEventListener('change', function(e) {
                            renderChart(e.target.value);
                        });
                    });
                </script>
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
<?php /**PATH C:\xampp\htdocs\AiAutoGrader - Final\resources\views/admin/analytics/index.blade.php ENDPATH**/ ?>