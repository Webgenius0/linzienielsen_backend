<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create 5 users
        $users = [
            [
                'name' => 'User One',
                'handle' => 'userone123',
                'email' => 'user1@user.com',
                'password' => bcrypt('12345678'),
                'avatar' => 'avatar1.png',
                'role' => 'user',
                'status' => true,
                'subscribe' => false,
            ],
            [
                'name' => 'User Two',
                'handle' => 'usertwo123',
                'email' => 'user2@user.com',
                'password' => bcrypt('12345678'),
                'avatar' => 'avatar2.png',
                'role' => 'user',
                'status' => true,
                'subscribe' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'User Three',
                'handle' => 'userthree123',
                'email' => 'user3@user.com',
                'password' => bcrypt('12345678'),
                'avatar' => 'avatar3.png',
                'role' => 'user',
                'status' => true,
                'subscribe' => false,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'User Four',
                'handle' => 'userfour123',
                'email' => 'user4@user.com',
                'password' => bcrypt('12345678'),
                'avatar' => 'avatar4.png',
                'role' => 'user',
                'status' => true,
                'subscribe' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'User Five',
                'handle' => 'userfive123',
                'email' => 'user5@user.com',
                'password' => bcrypt('12345678'),
                'avatar' => 'avatar5.png',
                'role' => 'user',
                'status' => true,
                'subscribe' => false,
                'email_verified_at' => now(),
            ],
        ];

        // Insert users
        foreach ($users as $userData) {
            $user = User::create($userData);

            // Create profile for each user
            Profile::create([
                'user_id' => $user->id,
                'gender' => 'male',
                'country' => 'USA',
                'date_of_birth' => '1990-01-01',
            ]);
        }

        // Create 1 admin user
        $admin = User::create([
            'name' => 'Admin User',
            'handle' => 'admin123',
            'email' => 'admin@admin.com',
            'password' => bcrypt('adminpassword'),
            'avatar' => 'adminavatar.png',
            'role' => 'admin',
            'status' => true,
            'subscribe' => false,
            'email_verified_at' => now(),
        ]);

        // Create profile for the admin
        Profile::create([
            'user_id' => $admin->id,
            'gender' => 'male',
            'country' => 'USA',
            'date_of_birth' => '1985-01-01',
        ]);
    }
}
