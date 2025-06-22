<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
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
                'email' => 'admin@admin.com',
                'password' => Hash::make('12345678'),
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
