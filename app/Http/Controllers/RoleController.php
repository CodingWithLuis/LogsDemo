<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->paginate(15);

        return view('roles.index', ['roles' => $roles]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('roles.create', ['role' => null]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        try {
            $role = Role::create([
                'name' => $request->validated('name'),
                'slug' => $this->uniqueSlug($request->validated('name')),
            ]);

            return redirect()->route('roles.index')
                ->with('success', "El rol {$role->name} se ha creado correctamente.");
        } catch (Throwable $e) {
            return redirect()->route('roles.create')
                ->withInput()
                ->with('error', 'Algo salió mal al crear el rol. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): View
    {
        $role->loadCount('users');

        return view('roles.show', ['role' => $role]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): View
    {
        return view('roles.edit', ['role' => $role]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        try {
            $role->update([
                'name' => $request->validated('name'),
                'slug' => $this->uniqueSlug($request->validated('name'), $role),
            ]);

            return redirect()->route('roles.index')
                ->with('success', "El rol {$role->name} se ha actualizado correctamente.");
        } catch (Throwable $e) {
            return redirect()->route('roles.edit', $role)
                ->withInput()
                ->with('error', 'Algo salió mal al actualizar este rol. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        try {
            $name = $role->name;

            $role->delete();

            return redirect()->route('roles.index')
                ->with('success', "El rol {$name} se ha eliminado correctamente.");
        } catch (Throwable $e) {
            return back()->with('error', 'Algo salió mal al eliminar este rol. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Build a unique slug for the given name, appending a numeric suffix on collision.
     */
    private function uniqueSlug(string $name, ?Role $ignore = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        $query = Role::query()->where('slug', $slug);

        if ($ignore !== null) {
            $query->where('id', '!=', $ignore->id);
        }

        while ($query->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;

            $query->where('slug', $slug);
        }

        return $slug;
    }
}
