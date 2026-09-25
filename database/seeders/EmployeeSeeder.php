<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EmployeeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a set of demo employees.
     */
    public function run(): void
    {
        Employee::factory()->count(10)->create();

        Log::info('Seeded demo employees.', [
            'count' => 10,
        ]);
    }
}
