@php($editing = isset($user) && $user !== null)

<div class="grid gap-4">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required
            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
        @error('name')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
        @error('email')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="role_id" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Role</label>
        <select id="role_id" name="role_id" required
            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
            <option value="">Select a role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id ?? null) == $role->id)>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        @error('role_id')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">
            Password
            @if ($editing)
                <span class="font-normal text-neutral-400">(leave blank to keep the current one)</span>
            @endif
        </label>
        <input type="password" id="password" name="password" autocomplete="new-password"
            {{ $editing ? '' : 'required' }}
            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
        @error('password')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-200">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
            {{ $editing ? '' : 'required' }}
            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-neutral-500 focus:ring-2 focus:ring-neutral-400 focus:outline-hidden dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
    </div>
</div>