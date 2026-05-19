<x-layouts.app :title="'Table Repository — ' . $story->title">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">Table Repository</h1>
            <p class="text-body-sm text-txt-muted mt-1">{{ $story->title }}</p>
        </div>
        <a href="{{ route('stories.show', $story) }}" class="btn-secondary">Back to Story</a>
    </div>

    <div class="page-body">
        @if ($tables->isEmpty())
            <div class="card text-center py-12">
                <p class="text-txt-muted">No tables in repository yet. Tables are populated when a story is approved.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($tables as $table)
                    <a href="{{ route('stories.repository.show', [$story, $table]) }}"
                        class="card hover:border-brand-primary transition-colors cursor-pointer block">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-heading font-bold text-body font-mono text-brand-blue">
                                {{ $table->table_name }}</h3>
                            <span
                                class="badge-{{ $table->table_type === 'master' ? 'info' : 'warning' }}">{{ ucfirst($table->table_type) }}</span>
                        </div>
                        @if ($table->description)
                            <p class="text-body-sm text-txt-secondary mb-3">{{ \Str::limit($table->description, 100) }}
                            </p>
                        @endif
                        <p class="text-caption text-txt-muted">{{ $table->columns->count() }} columns</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
