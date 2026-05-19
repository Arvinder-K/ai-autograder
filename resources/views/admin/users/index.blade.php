<x-layouts.app :title="'User Management — Admin'">
    <div class="page-header">
        <h1 class="font-heading font-bold text-title-lg">User Management</h1>
    </div>

    <div class="page-body">
        {{-- Filters --}}
        <div class="card mb-6">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
                        class="form-input">
                </div>
                <div class="w-48">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-input">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->slug }}" {{ request('role') === $role->slug ? 'selected' : '' }}>
                                {{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="btn-ghost">Clear</a>
            </form>
        </div>

        <div class="card p-0 overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Provider</th>
                        <th>Last Login</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr x-data="{ editing: false }">
                            <td class="font-heading font-semibold">{{ $user->name }}</td>
                            <td class="text-txt-secondary">{{ $user->email }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->roles as $role)
                                        <span class="badge-info">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-caption">{{ ucfirst($user->provider ?? 'local') }}</td>
                            <td class="text-caption text-txt-muted">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                            <td class="text-center">
                                <span
                                    class="badge-{{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}"
                                        class="inline">
                                        @csrf
                                        <button type="submit" class="btn-ghost text-caption"
                                            onclick="return confirm('Toggle user status?')">
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    </div>
</x-layouts.app>
