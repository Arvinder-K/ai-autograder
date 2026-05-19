<x-layouts.app :title="'Technical Designs — ' . $story->title">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">Technical Designs</h1>
            <p class="text-body-sm text-txt-muted mt-1">{{ $story->title }}</p>
        </div>
        <a href="{{ route('stories.show', $story) }}" class="btn-secondary">Back to Story</a>
    </div>

    <div class="page-body">
        @if ($story->featureLists->count() > 0)
            <div class="card mb-6">
                <h2 class="card-header">Generate Technical Design</h2>
                <form method="POST" action="{{ route('stories.designs.generate', $story) }}"
                    class="flex items-end gap-4">
                    @csrf
                    <div class="flex-1">
                        <label class="form-label">Target Audience</label>
                        <select name="audience" class="form-input">
                            <option value="both">Both Technical & Business</option>
                            <option value="technical">Technical Team Only</option>
                            <option value="business">Business Stakeholders Only</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-accent">Generate Design</button>
                </form>
            </div>
        @endif

        @forelse($designs as $design)
            <div class="card mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-heading font-bold text-title-sm">{{ $design->title }}</h3>
                        <p class="text-caption text-txt-muted">
                            {{ $design->creator->name ?? 'Unknown' }} &middot;
                            {{ $design->created_at->format('M d, Y') }}
                            &middot; <span class="badge-info">{{ ucfirst($design->audience) }}</span>
                        </p>
                    </div>
                    <a href="{{ route('stories.designs.show', [$story, $design]) }}"
                        class="btn-secondary text-caption">View Full</a>
                </div>
                <div class="text-body-sm text-txt-secondary line-clamp-4 whitespace-pre-wrap">
                    {{ \Str::limit($design->system_architecture, 500) }}
                </div>
            </div>
        @empty
            <div class="card text-center py-12">
                <p class="text-txt-muted">No technical designs yet.</p>
                @if ($story->featureLists->count() === 0)
                    <p class="text-body-sm text-txt-muted mt-2">Generate feature lists first before creating a technical
                        design.</p>
                @endif
            </div>
        @endforelse
    </div>
</x-layouts.app>
