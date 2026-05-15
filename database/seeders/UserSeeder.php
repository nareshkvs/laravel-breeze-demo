<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN->value,
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::USER->value,
        ]);

        User::create([
            'name' => 'Naresh',
            'email' => 'naresh@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN->value,
        ]);

        User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::USER->value,
        ]);

    }
}
