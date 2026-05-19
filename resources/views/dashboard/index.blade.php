<x-layouts.app :title="'Dashboard — AI Agent - Auto Grader'">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">Dashboard</h1>
            <p class="text-body-sm text-txt-muted mt-1">Welcome back, {{ Auth::user()->name }}</p>
        </div>
    </div>

    <div class="page-body">
        {{-- API Connection Details --}}
        <div class="mb-6">
            <div class="card p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-caption text-txt-muted uppercase tracking-wider font-semibold">API Connection</p>
                        <p class="text-body-sm mt-2 text-txt-primary">{{ $apiStatus['message'] }}</p>
                    </div>
                    <span class="badge-{{ $apiStatus['connected'] ? 'success' : 'danger' }}">
                        {{ $apiStatus['connected'] ? 'Connected' : 'Not Connected' }}
                    </span>
                </div>
            </div>
        </div>


    </div>
</x-layouts.app>
