<x-layouts.app :title="'Configuration — Admin'">
    <div class="page-header">
        <h1 class="font-heading font-bold text-title-lg">System Configuration</h1>
    </div>

    <div class="page-body">
        <form method="POST" action="{{ route('admin.config.update') }}">
            @csrf

            @foreach ($configs as $group => $items)
                <div class="card mb-6">
                    <h2 class="card-header">{{ ucfirst($group) }} Settings</h2>
                    <div class="space-y-4">
                        @foreach ($items as $config)
                            <div>
                                <label class="form-label">{{ $config->label ?? $config->key }}</label>
                                @if ($config->type === 'boolean')
                                    <select name="config[{{ $config->id }}]" class="form-input w-48">
                                        <option value="1" {{ $config->value ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ !$config->value ? 'selected' : '' }}>No</option>
                                    </select>
                                @elseif($config->type === 'json')
                                    <textarea name="config[{{ $config->id }}]" rows="4" class="form-input font-mono text-body-sm">{{ is_array($config->value) ? json_encode($config->value, JSON_PRETTY_PRINT) : $config->value }}</textarea>
                                @elseif($config->type === 'integer')
                                    <input type="number" name="config[{{ $config->id }}]"
                                        value="{{ $config->value }}" class="form-input w-48">
                                @else
                                    <input type="text" name="config[{{ $config->id }}]"
                                        value="{{ $config->value }}" class="form-input">
                                @endif
                                <p class="form-help">Key: {{ $config->group }}.{{ $config->key }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Save Configuration</button>
            </div>
        </form>
    </div>
</x-layouts.app>
