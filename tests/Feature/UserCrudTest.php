<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_when_managing_users(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
        $this->get(route('users.create'))->assertRedirect(route('login'));
        $this->post(route('users.store'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_list_users(): void
    {
        $admin = $this->admin();

        $role = Role::factory()->create(['name' => 'Manager', 'slug' => 'manager']);
        $manager = User::factory()->create(['name' => 'Jane Doe', 'role_id' => $role->id]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee($manager->email);
    }

    public function test_user_cannot_be_created_without_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'role_id']);
    }

    public function test_user_cannot_be_created_with_a_duplicate_email(): void
    {
        $role = Role::factory()->create(['name' => 'Employee', 'slug' => 'employee']);
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name' => 'New User',
                'email' => 'taken@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'role_id' => $role->id,
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_authenticated_users_can_create_a_user_with_a_role(): void
    {
        $role = Role::factory()->create(['name' => 'Manager', 'slug' => 'manager']);

        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name' => 'Carlos Lopez',
                'email' => 'carlos@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
                'role_id' => $role->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'carlos@example.com',
            'role_id' => $role->id,
        ]);
    }

    public function test_authenticated_users_can_update_a_user(): void
    {
        $role = Role::factory()->create(['name' => 'Manager', 'slug' => 'manager']);
        $user = User::factory()->create(['name' => 'Old Name', 'role_id' => $role->id]);

        $this->actingAs($this->admin())
            ->put(route('users.update', $user), [
                'name' => 'New Name',
                'email' => $user->email,
                'role_id' => $role->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_authenticated_users_can_delete_another_user(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['email' => 'target@example.com']);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_users_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertModelExists($admin);
    }

    private function admin(): User
    {
        $role = Role::factory()->create(['name' => 'Admin', 'slug' => 'admin']);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
