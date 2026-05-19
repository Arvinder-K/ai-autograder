<x-layouts.app :title="'Features — ' . $story->title">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">Feature Lists</h1>
            <p class="text-body-sm text-txt-muted mt-1">{{ $story->title }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stories.show', $story) }}" class="btn-secondary">Back to Story</a>
        </div>
    </div>

    <div class="page-body">
        @if ($story->isApproved())
            <div class="card mb-6">
                <h2 class="card-header">Generate New Feature List</h2>
                <form method="POST" action="{{ route('stories.features.generate', $story) }}"
                    class="flex items-end gap-4">
                    @csrf
                    <div class="flex-1">
                        <label class="form-label">Format Type</label>
                        <select name="format_type" class="form-input">
                            <option value="business">Format-01: Business User</option>
                            <option value="procode">Format-02: Pro-Code Development</option>
                            <option value="agentic">Format-03: Agentic AI Development</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-accent">Generate</button>
                </form>
            </div>
        @endif

        @if ($featureLists->isEmpty())
            <div class="card text-center py-12">
                <p class="text-txt-muted">No feature lists generated yet.</p>
                @if (!$story->isApproved())
                    <p class="text-body-sm text-txt-muted mt-2">Story must be approved before generating feature lists.
                    </p>
                @endif
            </div>
        @else
            <div class="grid gap-6">
                @foreach ($featureLists as $featureList)
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-heading font-bold text-title-sm">{{ $featureList->title }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span
                                        class="badge-{{ $featureList->format_type === 'business' ? 'info' : ($featureList->format_type === 'procode' ? 'success' : 'warning') }}">
                                        {{ $featureList->format_type === 'business' ? 'Format-01' : ($featureList->format_type === 'procode' ? 'Format-02' : 'Format-03') }}
                                    </span>
                                    <span class="text-caption text-txt-muted">{{ $featureList->items->count() }}
                                        features</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('stories.features.show', [$story, $featureList]) }}"
                                    class="btn-secondary text-caption">View</a>
                                <a href="{{ route('stories.features.export', [$story, $featureList]) }}"
                                    class="btn-accent text-caption">Export Excel</a>
                            </div>
                        </div>

                        {{-- Preview first few items --}}
                        @if ($featureList->items->count() > 0)
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Feature Cluster</th>
                                        <th>Feature</th>
                                        <th>Table</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($featureList->items->take(5) as $item)
                                        <tr>
                                            <td>{{ $item->sr_no }}</td>
                                            <td class="font-medium">{{ $item->feature_cluster }}</td>
                                            <td>{{ $item->feature }}</td>
                                            <td class="text-caption font-mono">{{ $item->table_name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if ($featureList->items->count() > 5)
                                <p class="text-center text-caption text-txt-muted mt-3">
                                    + {{ $featureList->items->count() - 5 }} more features.
                                    <a href="{{ route('stories.features.show', [$story, $featureList]) }}"
                                        class="text-brand-primary hover:underline">View all</a>
                                </p>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
