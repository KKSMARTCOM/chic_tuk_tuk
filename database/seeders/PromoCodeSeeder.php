<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PromoCode::create([
            'code' => 'BIENVENUE2025',
            'type' => 'percentage',
            'value' => 20,
            'max_uses' => 100,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(3),
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'FIRST1000',
            'type' => 'fixed',
            'value' => 1000,
            'max_uses' => 50,
            'valid_from' => now(),
            'valid_until' => now()->addMonth(),
            'is_active' => true,
        ]);
    }
}
