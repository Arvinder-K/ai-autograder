<x-layouts.app :title="'Dashboard — AI Auto Grader'">
    <div class="page-header">
        <div>
            <h1 class="text-display font-bold text-white bg-clip-text text-transparent bg-gradient-to-r from-brand-primary to-brand-accent">Dashboard</h1>
            <p class="text-body text-txt-secondary mt-2">Welcome back, {{ Auth::user() ? Auth::user()->name : 'Guest' }}</p>
        </div>
        <div class="hidden md:block">
            <button class="btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Evaluation
            </button>
        </div>
    </div>

    <div class="page-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            {{-- API Connection Card --}}
            <div class="card group">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-brand-primary/20 text-brand-primary flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-title-sm font-semibold text-white mb-1">API Connection</h3>
                        <p class="text-sm text-txt-muted">{{ $apiStatus['message'] ?? 'Service active' }}</p>
                    </div>
                    <span class="badge-{{ (isset($apiStatus['connected']) && $apiStatus['connected']) ? 'success' : 'danger' }} mt-1">
                        {{ (isset($apiStatus['connected']) && $apiStatus['connected']) ? 'Connected' : 'Not Connected' }}
                    </span>
                </div>
            </div>

            {{-- Evaluations Card --}}
            <div class="card group">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-brand-accent/20 text-brand-accent flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-title-sm font-semibold text-white mb-1">Evaluations</h3>
                        <p class="text-sm text-txt-muted">System load normal</p>
                    </div>
                    <span class="text-title font-bold text-white mt-1">
                        12
                    </span>
                </div>
            </div>

            {{-- Storage Card --}}
            <div class="card group">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-brand-violet/20 text-brand-violet flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                            </svg>
                        </div>
                        <h3 class="text-title-sm font-semibold text-white mb-1">Storage</h3>
                        <p class="text-sm text-txt-muted">Available capacity</p>
                    </div>
                    <span class="text-title font-bold text-white mt-1">
                        74%
                    </span>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 class="card-header text-white">Recent Activity</h2>
            <div class="py-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-surface-panel-muted text-txt-muted mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-white mb-1">No recent activity</h3>
                <p class="text-txt-secondary">When you start evaluating assignments, they will appear here.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
