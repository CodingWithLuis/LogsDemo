<x-layouts::app :title="__('Roles')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-neutral-900 dark:text-white">Roles</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Define the roles assigned to users.</p>
            </div>

            <a href="{{ route('roles.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-neutral-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200">
                New role
            </a>
        </div>

        @include('partials.flash')

        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr class="text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Users</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-900">
                    @forelse ($roles as $role)
                        <tr class="text-sm">
                            <td class="px-4 py-3 font-medium text-neutral-900 dark:text-white">{{ $role->name }}</td>
                            <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $role->slug }}</td>
                            <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $role->users_count }}</td>
                            <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $role->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('roles.show', $role) }}" class="font-medium text-neutral-700 hover:underline dark:text-neutral-200">View</a>
                                    <a href="{{ route('roles.edit', $role) }}" class="font-medium text-neutral-700 hover:underline dark:text-neutral-200">Edit</a>
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:underline dark:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                No roles yet. Create the first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $roles->links() }}</div>
    </div>
</x-layouts::app>