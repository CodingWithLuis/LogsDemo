<x-layouts::app :title="__('Role Details')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <a href="{{ route('roles.index') }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">
                &larr; Back to roles
            </a>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-white">{{ $role->name }}</h1>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
                <dl class="grid gap-4 text-sm">
                    <div>
                        <dt class="text-neutral-500 dark:text-neutral-400">Name</dt>
                        <dd class="mt-0.5 font-medium text-neutral-900 dark:text-white">{{ $role->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500 dark:text-neutral-400">Slug</dt>
                        <dd class="mt-0.5 font-medium text-neutral-900 dark:text-white">{{ $role->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500 dark:text-neutral-400">Users</dt>
                        <dd class="mt-0.5 font-medium text-neutral-900 dark:text-white">{{ $role->users_count }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500 dark:text-neutral-400">Created</dt>
                        <dd class="mt-0.5 font-medium text-neutral-900 dark:text-white">{{ $role->created_at->format('F j, Y g:i A') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex items-start gap-3">
                <a href="{{ route('roles.edit', $role) }}"
                    class="inline-flex items-center rounded-lg bg-neutral-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200">
                    Edit role
                </a>
                @if ($role->users_count === 0)
                    <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/40">
                            Delete role
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>