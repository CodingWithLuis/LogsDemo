<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            EmployeeSeeder::class,
            ProductSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Demo Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role_id' => Role::query()->where('slug', 'admin')->value('id'),
        ]);

        User::factory()->create([
            'name' => 'Demo Manager',
            'email' => 'manager@example.com',
            'password' => 'password',
            'role_id' => Role::query()->where('slug', 'manager')->value('id'),
        ]);

        Log::info('Demo database seeded successfully.', [
            'users' => User::count(),
            'employees' => Employee::count(),
            'products' => Product::count(),
        ]);
    }
}
