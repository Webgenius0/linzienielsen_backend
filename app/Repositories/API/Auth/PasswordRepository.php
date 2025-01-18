<?php

namespace App\Repositories\API\Auth;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordRepository implements PasswordRepositoryInterface
{

    /**
     * Changes the user's password for the given email.
     *
     * Updates the user's password in the database after hashing it.
     *
     * @param string $email The email of the user whose password is to be changed.
     * @param string $password The new password to be set for the user.
     *
     * @return bool Returns true if the password was successfully updated.
     */
    public function changePassword(string $email, string $password): bool
    {
        try {
            $user = User::where('email', $email)->first();
            $user->update([
                'password' => Hash::make($password),
            ]);
            return true;
        } catch (Exception $e) {
            Log::error('PasswordRepository::changePassword', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
