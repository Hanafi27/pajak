<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'petugas'],
            [
                'name' => 'Petugas Bapenda',
                'email' => 'petugas@bapenda.local',
                'password' => Hash::make('password123'),
                'role' => 'petugas',
            ]
        );

        User::updateOrCreate(
            ['username' => 'pimpinan'],
            [
                'name' => 'Pimpinan Bapenda',
                'email' => 'pimpinan@bapenda.local',
                'password' => Hash::make('password123'),
                'role' => 'pimpinan',
            ]
        );
    }
}
