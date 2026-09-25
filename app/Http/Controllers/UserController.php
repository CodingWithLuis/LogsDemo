<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::query()
            ->with('role')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('users.create', ['roles' => $this->orderedRoles()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        try {
            $user = User::create($request->safe()->only(['name', 'email', 'password', 'role_id']));

            Log::info('User created.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'performed_by' => auth()->id() ?? null,
            ]);

            return redirect()->route('users.index')
                ->with('success', "El usuario {$user->name} se ha creado correctamente.");
        } catch (Throwable $e) {
            Log::error('Failed to create user.', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
                'performed_by' => auth()->id() ?? null,
            ]);

            return redirect()->route('users.create')
                ->withInput()
                ->with('error', 'Algo salió mal al crear el usuario. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        return view('users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => $this->orderedRoles(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $attributes = $request->safe()->only(['name', 'email', 'role_id']);

            if ($request->filled('password')) {
                $attributes['password'] = $request->input('password');
            }

            $user->update($attributes);

            Log::info('User updated.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'performed_by' => auth()->id() ?? null,
            ]);

            return redirect()->route('users.index')
                ->with('success', "El usuario {$user->name} se ha actualizado correctamente.");
        } catch (Throwable $e) {
            Log::error('Failed to update user.', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'email' => $user->email,
                'performed_by' => auth()->id() ?? null,
            ]);

            return redirect()->route('users.edit', $user)
                ->withInput()
                ->with('error', 'Algo salió mal al actualizar este usuario. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            Log::warning('User attempted to delete their own account.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        try {
            $email = $user->email;

            $user->delete();

            Log::info('User deleted.', [
                'user_id' => $user->id,
                'email' => $email,
                'performed_by' => auth()->id() ?? null,
            ]);

            return redirect()->route('users.index')
                ->with('success', "El usuario {$email} se ha eliminado correctamente.");
        } catch (Throwable $e) {
            Log::error('Failed to delete user.', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'email' => $user->email,
                'performed_by' => auth()->id() ?? null,
            ]);

            return back()->with('error', 'Algo salió mal al eliminar este usuario. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * @return Collection<int, Role>
     */
    private function orderedRoles(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }
}
