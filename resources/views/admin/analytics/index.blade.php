<x-layouts.app :title="'Analytics — Admin'">
    <div class="page-header">
        <h1 class="font-heading font-bold text-title-lg">Analytics Dashboard</h1>
    </div>

    <div class="page-body">
        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card">
                <p class="text-caption text-txt-muted font-heading uppercase tracking-wider">Total Users</p>
                <p class="text-title-lg font-heading font-bold mt-1">{{ number_format($kpis['total_users']) }}</p>
            </div>
            <div class="card">
                <p class="text-caption text-txt-muted font-heading uppercase tracking-wider">Total Stories</p>
                <p class="text-title-lg font-heading font-bold mt-1">{{ number_format($kpis['total_stories']) }}</p>
            </div>
            <div class="card">
                <p class="text-caption text-txt-muted font-heading uppercase tracking-wider">Approved Stories</p>
                <p class="text-title-lg font-heading font-bold text-status-success-text mt-1">
                    {{ number_format($kpis['approved_stories']) }}</p>
            </div>
            <div class="card">
                <p class="text-caption text-txt-muted font-heading uppercase tracking-wider">Feature Lists</p>
                <p class="text-title-lg font-heading font-bold mt-1">{{ number_format($kpis['total_feature_lists']) }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Stories by Status --}}
            <div class="card">
                <h2 class="card-header">Stories by Status</h2>
                <div class="space-y-3 mt-4">
                    @foreach ($storiesByStatus as $item)
                        @php
                            $pct =
                                $kpis['total_stories'] > 0 ? round(($item->count / $kpis['total_stories']) * 100) : 0;
                            $color = match ($item->status) {
                                'approved' => 'bg-status-success-bg',
                                'draft' => 'bg-brand-accent',
                                default => 'bg-brand-primary',
                            };
                        @endphp
                        <div>
                            <div class="flex justify-between text-body-sm mb-1">
                                <span class="font-heading font-semibold">{{ ucfirst($item->status) }}</span>
                                <span class="text-txt-muted">{{ $item->count }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-surface-panel-muted rounded-full h-2">
                                <div class="{{ $color }} h-2 rounded-full" style="width: {{ $pct }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top Users --}}
            <div class="card">
                <h2 class="card-header">Top Users</h2>
                <div class="space-y-3 mt-4">
                    @foreach ($topUsers as $user)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-heading font-semibold text-body-sm">{{ $user->name }}</p>
                                <p class="text-caption text-txt-muted">{{ $user->email }}</p>
                            </div>
                            <span class="badge-info">{{ $user->stories_count }} stories</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Stories by Domain --}}
            <div class="card lg:col-span-2">
                <h2 class="card-header">Stories by Domain</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mt-4">
                    @foreach ($storiesByDomain as $domain => $count)
                        <div class="p-3 rounded-lg bg-surface-page text-center">
                            <p class="text-title font-heading font-bold text-brand-primary">{{ $count }}</p>
                            <p class="text-caption text-txt-muted mt-1">{{ $domain }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
