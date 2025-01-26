<?php

namespace App\Repositories\API\V1\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Creates a new user with the provided credentials.
     *
     * @param array $credentials The user data (first_name, last_name, email, password).
     *
     * @return User The created user object.
     */
    public function createUser(array $credentials):User;

    /**
     * Attempts to retrieve a user by their login credentials.
     *
     * @param array $credentials The user's login credentials (email and password).
     *
     * @return User|null The user object if found, null otherwise.
     */
    public function login(array $credentials):User|null;
}
