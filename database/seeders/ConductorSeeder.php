<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ConductorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Conducteurs
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Agent $i",
                'email' => "agent$i@chictuktuk.bj",
                'phone' => "+22990000" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
                'role' => 'driver',
            ]);

            Driver::create([
                'user_id' => $user->id,
                'license_number' => 'LIC' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'vehicle_number' => 'TRI' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'vehicle_type' => 'tricycle',
                'rating' => rand(40, 50) / 10,
                'total_trips' => rand(50, 200),
            ]);
        }

        // Clients
        /* for ($i = 1; $i <= 20; $i++) {
            User::create([
                'name' => "Client $i",
                'email' => "client$i@example.com",
                'phone' => "+22991000" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
                'role' => 'client',
            ]);
        } */
    }
}
