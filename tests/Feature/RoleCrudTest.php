<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_when_managing_roles(): void
    {
        $this->get(route('roles.index'))->assertRedirect(route('login'));
        $this->get(route('roles.create'))->assertRedirect(route('login'));
        $this->post(route('roles.store'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_list_roles(): void
    {
        Role::factory()->create(['name' => 'Manager', 'slug' => 'manager']);

        $this->actingAs($this->admin())
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Manager');
    }

    public function test_role_cannot_be_created_without_a_name(): void
    {
        $this->actingAs($this->admin())
            ->post(route('roles.store'), [])
            ->assertSessionHasErrors('name');
    }

    public function test_role_slug_is_generated_from_the_name(): void
    {
        $this->actingAs($this->admin())
            ->post(route('roles.store'), ['name' => 'Support'])
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'Support', 'slug' => 'support']);
    }

    public function test_roles_with_a_duplicate_name_get_a_unique_slug(): void
    {
        Role::factory()->create(['name' => 'Admin', 'slug' => 'admin']);

        $this->actingAs(User::factory()->create())
            ->post(route('roles.store'), ['name' => 'Admin'])
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'Admin', 'slug' => 'admin-2']);
    }

    public function test_authenticated_users_can_update_a_role(): void
    {
        $role = Role::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs($this->admin())
            ->put(route('roles.update', $role), ['name' => 'New Name'])
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'New Name', 'slug' => 'new-name']);
    }

    public function test_authenticated_users_can_delete_a_role(): void
    {
        $role = Role::factory()->create(['name' => 'Temporary', 'slug' => 'temporary']);

        $this->actingAs($this->admin())
            ->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_roles_with_assigned_users_cannot_be_deleted(): void
    {
        $role = Role::factory()->create(['name' => 'Employee', 'slug' => 'employee']);
        User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($this->admin())
            ->delete(route('roles.destroy', $role))
            ->assertSessionHas('error');

        $this->assertModelExists($role);
    }

    private function admin(): User
    {
        $role = Role::factory()->create(['name' => 'Admin', 'slug' => 'admin']);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
