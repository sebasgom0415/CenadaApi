<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@cenada.cr'],
            [
                'name'     => 'Administrador',
                'password' => \Illuminate\Support\Facades\Hash::make('cenada2026'),
            ]
        );
    }
}
