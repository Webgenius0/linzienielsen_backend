<?php

namespace App\Repositories\API\Auth;

use App\Exceptions\OTPNotVerifiedException;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgetPasswordRepository implements ForgetPasswordRepositoryInterface
{

    /**
     * Resets the user's password after validating the email and OTP.
     *
     * Updates the user's password and deletes the used OTP. Rolls back changes if an error occurs.
     *
     * @param array $credentials The user's email and new password.
     *
     * @return bool True if the reset is successful, otherwise throws an exception.
     */
    public function resetPassword(array $credentials): bool
    {
        try {
            DB::beginTransaction();

            $email = $credentials['email'];
            $password = Hash::make($credentials['password']);

            // Retrieve the user by email
            $user = User::whereEmail($email)->first();

            if (!$user) {
                throw new ModelNotFoundException('User Not Found', 404);
            }

            // Get the most recent OTP for password reset operation
            $userOTP = $user->otps()->whereOperation('password')->whereStatus(false)->first();

            if (!$userOTP) {
                throw new OTPNotVerifiedException();
            }

            // Update the password for the user
            $user->update(['password' => $password]);

            // Delete the OTP record after it has been successfully used
            $userOTP->delete();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
