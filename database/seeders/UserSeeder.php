<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@website.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Elliot',
                'email' => 'molefigw@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'User1',
                'email' => 'user@mail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'User2',
                'email' => 'user1@mail.com',
                'password' => '12345678',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => bcrypt($user['password']),
                ]
            );
        }
    }
}
