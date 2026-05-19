<x-layouts.app :title="'User Stories — AI Auto Grader'">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">User Stories</h1>
            <p class="text-body-sm text-txt-muted mt-1">Manage and view all user stories</p>
        </div>
        <a href="{{ route('stories.create') }}" class="btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Story
        </a>
    </div>

    <div class="page-body">
        {{-- Filters --}}
        <div class="card mb-6">
            <form method="GET" action="{{ route('stories.index') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search stories..."
                        class="form-input">
                </div>
                <div class="w-48">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="">All</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>In Review
                        </option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn-secondary">Filter</button>
                <a href="{{ route('stories.index') }}" class="btn-ghost">Clear</a>
            </form>
        </div>

        {{-- Stories Table --}}
        <div class="card p-0 overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Owner</th>
                        <th>Mode</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stories as $story)
                        <tr>
                            <td>
                                <a href="{{ route('stories.show', $story) }}"
                                    class="font-heading font-semibold text-brand-primary hover:underline">
                                    {{ $story->title }}
                                </a>
                            </td>
                            <td class="text-txt-secondary">{{ $story->user->name ?? 'Unknown' }}</td>
                            <td>
                                <span class="badge-{{ $story->creation_mode === 'quiz' ? 'info' : 'neutral' }}">
                                    {{ ucfirst($story->creation_mode) }}
                                </span>
                            </td>
                            <td class="text-txt-muted">v{{ $story->version }}</td>
                            <td>
                                <span
                                    class="badge-{{ $story->status === 'approved' ? 'success' : ($story->status === 'draft' ? 'warning' : 'info') }}">
                                    {{ ucfirst(str_replace('_', ' ', $story->status)) }}
                                </span>
                            </td>
                            <td class="text-txt-muted text-caption">{{ $story->created_at->format('M d, Y') }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('stories.show', $story) }}"
                                        class="btn-ghost text-caption px-2 py-1">View</a>
                                    @if ($story->isOwner(Auth::user()) && $story->isDraft())
                                        <a href="{{ route('stories.edit', $story) }}"
                                            class="btn-ghost text-caption px-2 py-1">Edit</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-txt-muted">No stories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $stories->links() }}
        </div>
    </div>
</x-layouts.app>
