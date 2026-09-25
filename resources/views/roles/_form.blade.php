<div class="grid gap-4">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $role->name ?? '') }}" required
            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
        @error('name')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-neutral-400">The slug is generated automatically from the name.</p>
    </div>
</div>