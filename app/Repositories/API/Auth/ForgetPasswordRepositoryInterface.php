<?php

namespace App\Repositories\API\Auth;

interface ForgetPasswordRepositoryInterface
{
    /**
     * Resets the user's password after verifying the email and OTP.
     *
     * @param array $credentials The user's email and new password.
     *
     * @return bool Returns true if the reset is successful.
     */
    public function resetPassword(array $credentials): bool;
}
