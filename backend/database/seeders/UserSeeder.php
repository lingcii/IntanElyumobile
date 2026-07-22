<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sanFernando = \App\Models\Municipality::where('name', 'San Fernando')->first();

        User::updateOrCreate(
            ['email' => 'picto@gaw.com'],
            [
                'name' => 'PICTO Admin',
                'password' => Hash::make('picto123'),
                'role' => 'picto',
                'status' => 'active',
                'municipality_id' => $sanFernando->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'lupto@gaw.com'],
            [
                'name' => 'LUPTO Admin',
                'password' => Hash::make('lupto123'),
                'role' => 'lupto',
                'status' => 'active',
                'municipality_id' => $sanFernando->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'sanfernando@gaw.com'],
            [
                'name' => 'San Fernando MTO',
                'password' => Hash::make('mto123'),
                'role' => 'san_fernando_mto',
                'status' => 'active',
                'municipality_id' => $sanFernando->id,
            ]
        );
    }
}
