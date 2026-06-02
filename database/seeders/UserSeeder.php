<?php

namespace Database\Seeders;

use App\Models\Role;
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
        $admin1 = User::create([
            'name' => 'Admin ChicTukTuk',
            'email' => 'kokamobilitysarl@gmail.com',
            'phone' => '+22990000000',
            'password' => Hash::make('Kok@mobilt/2026'),
            'profil' => 'admin',
        ]);

        $admin2 = User::create([
            'name' => 'Admin ChicTukTuk',
            'email' => 'ritoshi991@gmail.com',
            'phone' => '+22990000001',
            'password' => Hash::make('Password@2026'),
            'profil' => 'admin',
        ]);

        $adminRole = Role::where('name', 'admin')->first();

        $admin1->assignRole($adminRole);
        $admin2->assignRole($adminRole);
    }
}
