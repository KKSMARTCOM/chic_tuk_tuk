<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            'Cotonou',
            'Abomey-Calavi',
            'Ouidah',
            'Tori-Bossito',
            'Allada',
            'Zè',
        ];

        foreach ($zones as $zone) {
            Zone::updateOrCreate(
                ['slug' => Str::slug($zone)],
                [
                    'name' => $zone,
                    'is_active' => true,
                ]
            );
        }
    }
}
