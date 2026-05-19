<x-layouts.app :title="'Create Story — AI Auto Grader'">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">Create New Story</h1>
            <p class="text-body-sm text-txt-muted mt-1">Start by defining the basics of your user story</p>
        </div>
        <a href="{{ route('stories.index') }}" class="btn-secondary">Cancel</a>
    </div>

    <div class="page-body max-w-3xl">
        <form method="POST" action="{{ route('stories.store') }}">
            @csrf

            <div class="card mb-6">
                <h2 class="card-header">Story Details</h2>

                <div class="space-y-5">
                    <div>
                        <label for="title" class="form-label">Story Title <span
                                class="text-status-danger-text">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                            class="form-input" placeholder="e.g., Inventory Management System">
                        @error('title')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="form-label">Brief Description</label>
                        <textarea name="description" id="description" rows="4" class="form-input"
                            placeholder="Describe the business process or requirement...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Process Type <span class="text-status-danger-text">*</span></label>
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <label
                                class="relative flex items-start gap-3 p-4 rounded-lg border border-border cursor-pointer hover:border-brand-primary transition-colors">
                                <input type="radio" name="process_type" value="single" class="mt-0.5" checked>
                                <div>
                                    <p class="font-heading font-semibold text-body">Single Process</p>
                                    <p class="text-caption text-txt-muted mt-1">One focused business process</p>
                                </div>
                            </label>
                            <label
                                class="relative flex items-start gap-3 p-4 rounded-lg border border-border cursor-pointer hover:border-brand-primary transition-colors">
                                <input type="radio" name="process_type" value="multi" class="mt-0.5"
                                    {{ old('process_type') === 'multi' ? 'checked' : '' }}>
                                <div>
                                    <p class="font-heading font-semibold text-body">Multi-Process</p>
                                    <p class="text-caption text-txt-muted mt-1">Multiple interconnected processes</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-6">
                <h2 class="card-header">Creation Method</h2>
                <p class="text-body-sm text-txt-muted mb-4">Choose how you want to provide requirements</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label
                        class="relative flex items-start gap-3 p-5 rounded-lg border-2 border-border cursor-pointer hover:border-brand-primary transition-colors has-[:checked]:border-brand-primary has-[:checked]:bg-brand-primary/5">
                        <input type="radio" name="creation_mode" value="quiz" class="mt-0.5" checked>
                        <div>
                            <p class="font-heading font-bold text-body">Guided Quiz</p>
                            <p class="text-body-sm text-txt-muted mt-1">Answer structured questions across categories
                                like domain, stakeholders, AI features, and more.</p>
                            <span class="badge-info mt-3">Recommended</span>
                        </div>
                    </label>
                    <label
                        class="relative flex items-start gap-3 p-5 rounded-lg border-2 border-border cursor-pointer hover:border-brand-primary transition-colors has-[:checked]:border-brand-primary has-[:checked]:bg-brand-primary/5">
                        <input type="radio" name="creation_mode" value="description" class="mt-0.5"
                            {{ old('creation_mode') === 'description' ? 'checked' : '' }}>
                        <div>
                            <p class="font-heading font-bold text-body">Direct Description</p>
                            <p class="text-body-sm text-txt-muted mt-1">Write or paste your requirements directly. You
                                can also upload process documents.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('stories.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    Create Story & Continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
