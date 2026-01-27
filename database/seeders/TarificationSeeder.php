<?php

namespace Database\Seeders;

use App\Models\Pricing;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TarificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = Zone::pluck('id', 'name');

        $prices = [
            // Cotonou
            ['from' => 'Cotonou', 'to' => 'Cotonou', 'base_price' => 10000, 'duration' => 15],
            ['from' => 'Cotonou', 'to' => 'Abomey-Calavi', 'base_price' => 20000, 'duration' => 30],
            ['from' => 'Cotonou', 'to' => 'Ouidah', 'base_price' => 30000, 'duration' => 45],
            ['from' => 'Cotonou', 'to' => 'Tori-Bossito', 'base_price' => 35000, 'duration' => 50],

            // Abomey-Calavi
            ['from' => 'Abomey-Calavi', 'to' => 'Abomey-Calavi', 'base_price' => 8000, 'duration' => 15],
            ['from' => 'Abomey-Calavi', 'to' => 'Ouidah', 'base_price' => 15000, 'duration' => 25],
            ['from' => 'Abomey-Calavi', 'to' => 'Tori-Bossito', 'base_price' => 18000, 'duration' => 30],
            ['from' => 'Abomey-Calavi', 'to' => 'Cotonou', 'base_price' => 10000, 'duration' => 15],


            // Ouidah
            ['from' => 'Ouidah', 'to' => 'Ouidah', 'base_price' => 7000, 'duration' => 10],
            ['from' => 'Ouidah', 'to' => 'Tori-Bossito', 'base_price' => 12000, 'duration' => 20],
            ['from' => 'Ouidah', 'to' => 'Abomey-Calavi', 'base_price' => 15000, 'duration' => 25],
            ['from' => 'Ouidah', 'to' => 'Cotonou', 'base_price' => 10000, 'duration' => 15],

            //Tori-Bossito
            ['from' => 'Tori-Bossito', 'to' => 'Tori-Bossito', 'base_price' => 12000, 'duration' => 20],
            ['from' => 'Tori-Bossito', 'to' => 'Cotonou', 'base_price' => 10000, 'duration' => 15],
            ['from' => 'Tori-Bossito', 'to' => 'Abomey-Calavi', 'base_price' => 8000, 'duration' => 15],
            ['from' => 'Tori-Bossito', 'to' => 'Ouidah', 'base_price' => 7000, 'duration' => 10],
        ];

        foreach ($prices as $price) {
            Pricing::updateOrCreate(
                [
                    'from_zone_id' => $zones[$price['from']],
                    'to_zone_id' => $zones[$price['to']],
                ],
                [
                    'base_price' => $price['base_price'],
                    'price_per_km' => 0,
                    'estimated_duration' => $price['duration'],
                ]
            );
        }
    }
}
