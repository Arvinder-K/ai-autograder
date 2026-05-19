<x-layouts.app :title="$featureList->title">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">{{ $featureList->title }}</h1>
            <p class="text-body-sm text-txt-muted mt-1">
                {{ $story->title }} &middot;
                <span
                    class="badge-{{ $featureList->format_type === 'business' ? 'info' : ($featureList->format_type === 'procode' ? 'success' : 'warning') }}">
                    {{ ucfirst($featureList->format_type) }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stories.features.export', [$story, $featureList]) }}" class="btn-accent">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
            </a>
            <a href="{{ route('stories.features.index', $story) }}" class="btn-secondary">Back</a>
        </div>
    </div>

    <div class="page-body">
        <div class="card p-0 overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12">Sr.</th>
                        <th>Feature Cluster</th>
                        <th>Feature</th>
                        <th>Detailed Workflow</th>
                        <th>Feature Description</th>
                        <th>Table Name</th>
                        <th>Columns</th>
                        @if ($featureList->format_type === 'procode')
                            <th>Tech Stack</th>
                            <th>Actor</th>
                        @elseif($featureList->format_type === 'agentic')
                            <th>Agent Type</th>
                            <th>Actor</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($featureList->items as $item)
                        <tr>
                            <td class="font-heading font-semibold">{{ $item->sr_no }}</td>
                            <td>
                                <span
                                    class="inline-flex items-center px-2 py-1 bg-brand-primary/10 text-brand-primary text-caption font-heading font-semibold rounded">
                                    {{ $item->feature_cluster }}
                                </span>
                            </td>
                            <td class="font-medium">{{ $item->feature }}</td>
                            <td class="text-body-sm text-txt-secondary max-w-xs">
                                {{ \Str::limit($item->detailed_workflow, 100) }}</td>
                            <td class="text-body-sm text-txt-secondary max-w-xs">
                                {{ \Str::limit($item->feature_description, 100) }}</td>
                            <td class="text-caption font-mono text-brand-blue">{{ $item->table_name }}</td>
                            <td class="text-caption font-mono text-txt-muted max-w-xs">
                                {{ \Str::limit($item->table_column_names, 60) }}</td>
                            @if ($featureList->format_type === 'procode')
                                <td class="text-caption">{{ $item->technology_stack }}</td>
                                <td class="text-caption">{{ $item->actor_user }}</td>
                            @elseif($featureList->format_type === 'agentic')
                                <td class="text-caption">{{ $item->agent_type }}</td>
                                <td class="text-caption">{{ $item->actor_user }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
