<x-layouts.app :title="'Versions — ' . $story->title">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">Version History</h1>
            <p class="text-body-sm text-txt-muted mt-1">{{ $story->title }}</p>
        </div>
        <a href="{{ route('stories.show', $story) }}" class="btn-secondary">Back to Story</a>
    </div>

    <div class="page-body">
        <div class="card p-0 overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Title</th>
                        <th>Change Summary</th>
                        <th>Changed By</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($versions as $version)
                        <tr>
                            <td><span class="badge-info">v{{ $version->version_number }}</span></td>
                            <td class="font-heading font-semibold">{{ $version->title }}</td>
                            <td class="text-txt-secondary">{{ $version->change_summary }}</td>
                            <td class="text-txt-secondary">{{ $version->changedByUser->name ?? 'Unknown' }}</td>
                            <td class="text-txt-muted text-caption">{{ $version->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="text-right">
                                <form method="POST"
                                    action="{{ route('stories.versions.delete', [$story, $version->id]) }}"
                                    class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost text-caption text-status-danger-text"
                                        onclick="return confirm('Delete this version?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-txt-muted">No versions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $versions->links() }}</div>
    </div>
</x-layouts.app>
