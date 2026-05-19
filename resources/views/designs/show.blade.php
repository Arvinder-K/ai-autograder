<x-layouts.app :title="$design->title">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">{{ $design->title }}</h1>
            <p class="text-body-sm text-txt-muted mt-1">{{ $story->title }}</p>
        </div>
        <a href="{{ route('stories.designs.index', $story) }}" class="btn-secondary">Back</a>
    </div>

    <div class="page-body max-w-4xl">
        <div class="card mb-6">
            <h2 class="card-header">System Architecture & Design</h2>
            <div class="prose prose-sm max-w-none text-txt-secondary font-base whitespace-pre-wrap">
                {!! nl2br(e($design->system_architecture)) !!}
            </div>
        </div>

        @if ($design->presentation_content)
            <div class="card">
                <h2 class="card-header">Presentation Summary</h2>
                <div class="prose prose-sm max-w-none text-txt-secondary font-base whitespace-pre-wrap">
                    {!! nl2br(e($design->presentation_content)) !!}
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
