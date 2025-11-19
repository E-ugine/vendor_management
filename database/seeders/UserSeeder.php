<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\UserRole;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Initiator User', 'email' => 'initiator@test.com', 'role' => UserRole::INITIATOR],
            ['name' => 'Vendor User', 'email' => 'vendor@test.com', 'role' => UserRole::VENDOR],
            ['name' => 'Checker User', 'email' => 'checker@test.com', 'role' => UserRole::CHECKER],
            ['name' => 'Procurement User', 'email' => 'procurement@test.com', 'role' => UserRole::PROCUREMENT],
            ['name' => 'Legal User', 'email' => 'legal@test.com', 'role' => UserRole::LEGAL],
            ['name' => 'Finance User', 'email' => 'finance@test.com', 'role' => UserRole::FINANCE],
            ['name' => 'Director User', 'email' => 'director@test.com', 'role' => UserRole::DIRECTOR],
        ];

        foreach ($roles as $roleData) {
            User::create([
                'name' => $roleData['name'],
                'email' => $roleData['email'],
                'current_role' => $roleData['role']->value,
                'password' => Hash::make('password'), // All use 'password' for demo
            ]);
        }
    }
}