<x-layouts.app :title="$table->table_name . ' — Table Repository'">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg font-mono">{{ $table->table_name }}</h1>
            <p class="text-body-sm text-txt-muted mt-1">
                <span
                    class="badge-{{ $table->table_type === 'master' ? 'info' : 'warning' }}">{{ ucfirst($table->table_type) }}</span>
                &middot; {{ $table->columns->count() }} columns
            </p>
        </div>
        <a href="{{ route('stories.repository.index', $story) }}" class="btn-secondary">Back</a>
    </div>

    <div class="page-body">
        @if ($table->description)
            <div class="card mb-6">
                <p class="text-body text-txt-secondary">{{ $table->description }}</p>
            </div>
        @endif

        <div class="card p-0 overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Column Name</th>
                        <th>Data Type</th>
                        <th class="text-center">PK</th>
                        <th class="text-center">FK</th>
                        <th class="text-center">Nullable</th>
                        <th>References</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($table->columns as $column)
                        <tr>
                            <td class="font-mono font-semibold text-brand-blue">{{ $column->column_name }}</td>
                            <td class="font-mono text-caption">{{ $column->data_type }}</td>
                            <td class="text-center">
                                @if ($column->is_primary_key)
                                    <span class="badge-success">PK</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($column->is_foreign_key)
                                    <span class="badge-warning">FK</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($column->is_nullable)
                                    <span class="text-txt-muted">✓</span>
                                @endif
                            </td>
                            <td class="text-caption font-mono text-txt-secondary">
                                @if ($column->references_table)
                                    {{ $column->references_table }}.{{ $column->references_column }}
                                @endif
                            </td>
                            <td class="text-body-sm text-txt-secondary max-w-xs">{{ $column->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
