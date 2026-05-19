<x-layouts.app :title="'Audit Log — Admin'">
    <div class="page-header">
        <h1 class="font-heading font-bold text-title-lg">Audit Log</h1>
    </div>

    <div class="page-body">
        {{-- Filters --}}
        <div class="card mb-6">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="flex flex-wrap items-end gap-4">
                <div class="w-48">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-input">
                        <option value="">All</option>
                        @foreach (['login', 'logout', 'created', 'updated', 'deleted', 'approved', 'generated'] as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ ucfirst($action) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-48">
                    <label class="form-label">Entity Type</label>
                    <select name="entity_type" class="form-input">
                        <option value="">All</option>
                        @foreach (['Story', 'FeatureList', 'TechnicalDesign', 'CodingPrompt', 'User'] as $type)
                            <option value="{{ $type }}"
                                {{ request('entity_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-40">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
                </div>
                <div class="w-40">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
                </div>
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('admin.audit.index') }}" class="btn-ghost">Clear</a>
            </form>
        </div>

        <div class="card p-0 overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="text-caption text-txt-muted whitespace-nowrap">
                                {{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            <td class="font-heading font-semibold">{{ $log->user->name ?? 'System' }}</td>
                            <td><span class="badge-info">{{ $log->action }}</span></td>
                            <td class="text-body-sm text-txt-secondary">
                                {{ $log->entity_type }}
                                @if ($log->entity_id)
                                    <span class="text-txt-muted">#{{ $log->entity_id }}</span>
                                @endif
                            </td>
                            <td class="text-caption font-mono text-txt-muted">{{ $log->ip_address }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $logs->links() }}</div>
    </div>
</x-layouts.app>
