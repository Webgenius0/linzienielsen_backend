<?php

namespace App\Repositories\API\V1\Auth;

use App\Helpers\Helper;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{

    /**
     * Creates a new user and their associated profile.
     *
     * Registers a user with provided credentials, creates a unique user handle,
     * and assigns a default role (or a custom role). Additionally, a profile
     * is created for the user upon registration.
     *
     * @param array $credentials The user data (first_name, last_name, email, password).
     * @param string $role The role of the user (default is 'user').
     *
     * @return User The created user object.
     *
     * @throws Exception If user creation fails.
     */
    public function createUser(array $credentials, $role = 'user'):User
    {
        try {
            // creating user
            $user = User::create([
                'name' => $credentials['name'],
                'handle' => Helper::generateUniqueSlug($credentials['name'], 'users', 'handle'),
                'email' => $credentials['email'],
                'password' => Hash::make($credentials['password']),
                'role' => $role,
            ]);

            if (isset($credentials['avatar'])) {
                $url = Helper::uploadFile($credentials['avatar'], 'user/'. $user->id);
                $user->update([
                    'avatar' => $url,
                ]);
            }

            // creating user profile
            $user->profile()->create([
                'gender' => $credentials['gender'],
                'country' => $credentials['country'],
                'date_of_birth' => $credentials['date_of_birth'],
            ]);
            return $user;
        } catch (Exception $e) {
            Log::error('UserRepository::createUser', ['error' => $e->getMessage()]);
            throw $e;
        }
    }



    /**
     * Attempts to retrieve a user by their email address.
     *
     * This method checks the provided credentials and returns the corresponding user.
     *
     * @param array $credentials The user's login credentials (email and password).
     *
     * @return User|null The user object if found, null otherwise.
     *
     * @throws Exception If there is an error during the query.
     */
    public function login(array $credentials):User|null
    {
        try {
            return User::where('email', $credentials['email'])->first();
        } catch (Exception $e) {
            Log::error('UserRepository::login', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
