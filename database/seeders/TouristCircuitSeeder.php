<?php

namespace Database\Seeders;

use App\Models\TouristCircuit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TouristCircuitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Circuits touristiques
        TouristCircuit::create([
            'name' => 'Tour Historique de Porto-Novo',
            'description' => 'Découvrez l\'histoire et la culture de Porto-Novo avec nos guides experts.',
            'locations' => ['Musée Honmè', 'Grande Mosquée', 'Palais Royal', 'Marché Ouando'],
            'price' => 15000,
            'duration' => 4,
            'is_active' => true,
        ]);

        TouristCircuit::create([
            'name' => 'Visite des Plages de Cotonou',
            'description' => 'Un tour relaxant le long des plus belles plages de Cotonou.',
            'locations' => ['Plage de Fidjrossè', 'Plage de la Marina', 'Boulevard de la Marina'],
            'price' => 10000,
            'duration' => 3,
            'is_active' => true,
        ]);
    }
}
