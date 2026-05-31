<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin ChicTukTuk',
            'email' => 'kokamobilitysarl@gmail.com',
            'phone' => '+22990000000',
            'password' => Hash::make('Kok@mobilt/2026'),
            'role' => 'admin',
        ]);
    }
}
