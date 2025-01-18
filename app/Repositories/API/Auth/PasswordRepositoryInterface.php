<?php

namespace App\Repositories\API\Auth;

interface PasswordRepositoryInterface
{

    /**
     * Changes the user's password for the specified email.
     *
     * @param string $email The user's email address.
     * @param string $password The new password to set for the user.
     *
     * @return bool Returns true if the password was successfully changed.
     */
    public function changePassword(string $email, string $password): bool;
}
