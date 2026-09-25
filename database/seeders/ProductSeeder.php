<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a set of demo products.
     */
    public function run(): void
    {
        Product::factory()->count(20)->create();

        Log::info('Seeded demo products.', [
            'count' => 20,
        ]);
    }
}
