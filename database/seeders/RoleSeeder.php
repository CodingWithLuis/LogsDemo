<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        $roles = [
            'Admin' => 'admin',
            'Employee' => 'employee',
            'Manager' => 'manager',
        ];

        foreach ($roles as $name => $slug) {
            Role::query()->updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        Log::info('Seeded roles.', [
            'count' => count($roles),
            'roles' => array_values($roles),
        ]);
    }
}
