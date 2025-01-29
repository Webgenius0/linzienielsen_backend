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
                'avatar' => 'https://i.pinimg.com/736x/8a/af/ce/8aafcefe315b86cc0343c331fdc6d534.jpg',
                'role' => 'user',
                'status' => true,
                'subscribe' => false,
            ],
            [
                'name' => 'User Two',
                'handle' => 'usertwo123',
                'email' => 'user2@user.com',
                'password' => bcrypt('12345678'),
                'avatar' => 'https://i.pinimg.com/736x/61/bd/4a/61bd4a5603687eaf3fde676547a001ae.jpg',
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
                'avatar' => 'https://images4.alphacoders.com/148/148906.jpg',
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
                'avatar' => 'https://m.media-amazon.com/images/I/816KBQDoAgL.png',
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
                'avatar' => 'https://celebmafia.com/wp-content/uploads/2014/12/selena-gomez-march-of-dimes-celebration-of-babies-in-beverly-hills-december-2014_1.jpg',
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
            'password' => bcrypt('12345678'),
            'avatar' => 'https://thumbs.dreamstime.com/b/business-confident-portrait-girl-office-internship-as-secretary-company-admin-work-tasks-female-employee-happy-315217212.jpg',
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
