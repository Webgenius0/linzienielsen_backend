<?php

namespace App\Repositories\API\Auth;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class SocialLoginRepository implements SocialLoginRepositoryInterface
{

    /**
     * Finds a user by their email address.
     *
     * @param string $email The email address of the user.
     *
     * @return User|null The user object if found, null otherwise.
     */
    public function findByEmail(string $email):mixed
    {
        return User::whereEmail($email)->first();
    }


    /**
     * Creates a new user with the provided data.
     *
     * Begins a transaction, creates the user, and commits the transaction.
     * Rolls back if any exception occurs.
     *
     * @param array $data The user data (first_name, last_name, email, password, address).
     *
     * @return User The created user object.
     *
     * @throws Exception If user creation fails.
     */
    public function create(array $data):User
    {
        DB::beginTransaction();
        try {
            // Assuming 'authService->createUser' is replaced by direct user creation
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'address' => $data['address'] ?? null,
            ]);
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Attempts to log in a user with the given credentials.
     *
     * @param array $credentials The user's email and password.
     *
     * @return bool True if login is successful, false otherwise.
     */
    public function login(array $credentials):bool
    {
        // Assuming the login logic is abstracted within this method.
        return auth()->attempt($credentials);
    }
}
