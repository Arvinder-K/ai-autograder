<x-layouts.app :title="'Story Management — Admin'">
    <div class="page-header">
        <h1 class="font-heading font-bold text-title-lg">Story Management</h1>
    </div>

    <div class="page-body">
        <div class="card p-0 overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Owner</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Version</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stories as $story)
                        <tr>
                            <td>
                                <a href="{{ route('stories.show', $story) }}"
                                    class="font-heading font-semibold text-brand-primary hover:underline">{{ $story->title }}</a>
                            </td>
                            <td class="text-txt-secondary">{{ $story->user->name }}</td>
                            <td class="text-caption">{{ ucfirst($story->creation_mode) }}</td>
                            <td><span
                                    class="badge-{{ $story->status === 'approved' ? 'success' : ($story->status === 'draft' ? 'warning' : 'info') }}">{{ ucfirst($story->status) }}</span>
                            </td>
                            <td class="text-caption text-txt-muted">v{{ $story->version }}</td>
                            <td class="text-caption text-txt-muted">{{ $story->created_at->format('M d, Y') }}</td>
                            <td class="text-right">
                                @if ($story->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.stories.destroy', $story) }}"
                                        class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-ghost text-caption text-status-danger-text"
                                            onclick="return confirm('Delete this story?')">Delete</button>
                                    </form>
                                @else
                                    <span class="text-caption text-txt-muted">Protected</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $stories->links() }}</div>
    </div>
</x-layouts.app>
