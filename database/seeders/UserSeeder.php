<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
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
        $users = [
            [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'employee_id' => Str::random(10),
                'is_verified' => 'true',
            ],
            [
                'name' => 'user',
                'email' => 'user@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'email_verified_at' => now(),
                'employee_id' => Str::random(10),
                'is_verified' => 'true',
            ],
            [
                'name' => 'user2',
                'email' => 'user2@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'email_verified_at' => now(),
                'employee_id' => Str::random(10),
                'is_verified' => 'true',
            ],
            [
                'name' => 'user3',
                'email' => 'user3@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'email_verified_at' => now(),
                'employee_id' => Str::random(10),
                'is_verified' => 'true',
            ]
        ];
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
