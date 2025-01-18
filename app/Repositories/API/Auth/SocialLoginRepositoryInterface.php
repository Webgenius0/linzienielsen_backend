<?php

namespace App\Repositories\API\Auth;

use App\Models\User;

interface SocialLoginRepositoryInterface
{
    /**
     * Finds a user by their email address.
     *
     * @param string $email The email address of the user.
     *
     * @return User|null The user object if found, null otherwise.
     */
    public function findByEmail(string $email):User;

    /**
     * Creates a new user with the provided data.
     *
     * @param array $data The user data (e.g., first_name, last_name, email, password, address).
     *
     * @return User The created user object.
     */
    public function create(array $data):User;

    /**
     * Attempts to log in a user with the provided credentials.
     *
     * @param array $credentials The user's email and password.
     *
     * @return bool True if login is successful, false otherwise.
     */
    public function login(array $credentials):User;
}
