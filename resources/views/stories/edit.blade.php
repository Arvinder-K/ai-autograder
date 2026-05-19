<x-layouts.app :title="'Edit Story — AI Auto Grader'">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">Edit Story</h1>
            <p class="text-body-sm text-txt-muted mt-1">{{ $story->title }}</p>
        </div>
        <a href="{{ route('stories.show', $story) }}" class="btn-secondary">Cancel</a>
    </div>

    <div class="page-body max-w-3xl">
        <form method="POST" action="{{ route('stories.update', $story) }}">
            @csrf @method('PUT')

            <div class="card mb-6">
                <h2 class="card-header">Story Details</h2>
                <div class="space-y-5">
                    <div>
                        <label for="title" class="form-label">Story Title <span
                                class="text-status-danger-text">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title', $story->title) }}"
                            required class="form-input">
                        @error('title')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="form-label">Brief Description</label>
                        <textarea name="description" id="description" rows="4" class="form-input">{{ old('description', $story->description) }}</textarea>
                        @error('description')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($story->generated_story)
                        <div>
                            <label for="generated_story" class="form-label">Generated User Story</label>
                            <textarea name="generated_story" id="generated_story" rows="20" class="form-input font-mono text-body-sm">{{ old('generated_story', $story->generated_story) }}</textarea>
                            @error('generated_story')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('stories.show', $story) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>

        {{-- Document Upload --}}
        <div class="card mt-6">
            <h2 class="card-header">Upload Process Document</h2>
            <form method="POST" action="{{ route('stories.upload', $story) }}" enctype="multipart/form-data"
                class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <input type="file" name="document" accept=".pdf,.doc,.docx,.txt,.md" class="form-input" required>
                    <p class="form-help">PDF, DOC, DOCX, TXT, MD (max 10MB)</p>
                </div>
                <button type="submit" class="btn-secondary">Upload & Analyze</button>
            </form>
        </div>
    </div>
</x-layouts.app>
