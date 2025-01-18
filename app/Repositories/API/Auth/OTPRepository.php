<?php

namespace App\Repositories\API\Auth;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\OTPExpiredException;
use App\Exceptions\OTPMismatchException;
use App\Exceptions\UserAlreadyVarifiedException;
use App\Jobs\SendOTPEmail;
use Exception;

class OTPRepository implements OTPRepositoryInterface
{
    /**
     * Validates and matches the OTP for a specified operation.
     *
     * Checks if the OTP is correct, not expired, and active. Invalidates the OTP after successful verification.
     *
     * @param User $user The user whose OTP is being validated.
     * @param string $operation The operation for which the OTP is generated.
     * @param string $otp The OTP provided by the user.
     *
     * @return bool True if the OTP is valid, otherwise throws an exception.
     */
    public function sendOtp(User $user, string $operation): int
    {
        try {
            // Delete any existing OTPs for the specified operation
            $user->otps()->whereOperation($operation)->delete();

            // Generate a new OTP
            $otp = mt_rand(111111, 999999);

            // Store OTP in database
            $user->otps()->create([
                'operation' => $operation,
                'number' => $otp,
            ]);

            // Dispatch the OTP sending email job
            $subject = $operation === 'email' ? 'Verify Email' : 'One Time Password (OTP)';
            SendOTPEmail::dispatch($user, $otp, $subject);

            return $otp;
        } catch (Exception $e) {
            Log::error('OTPRepository::sendOtp', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate and match the provided OTP for the given operation.
     *
     * @param User $user The user to verify the OTP for.
     * @param string $operation The operation for which the OTP was generated.
     * @param string $otp The OTP provided by the user.
     * @return bool True if OTP is valid, false otherwise.
     *
     * @throws OTPMismatchException If the OTP does not match.
     * @throws OTPExpiredException If the OTP has expired.
     * @throws UserAlreadyVarifiedException If the user has already verified the operation.
     * @throws \Exception If any unexpected error occurs.
     */
    public function matchOtp(User $user, string $operation, string $otp): bool
    {
        try {
            // Check if the OTP exists and is active for the given operation
            $userOtp = $user->otps()->whereOperation($operation)->whereStatus(true)->first();

            if (!$userOtp || (int) $otp !== $userOtp->number) {
                throw new OTPMismatchException();
            }

            // Check if OTP has expired (1 minute window)
            if ($userOtp->created_at->diffInMinutes(now()) > 1) {
                throw new OTPExpiredException();
            }

            // Invalidate OTP after it has been successfully used
            $userOtp->status = false;
            $userOtp->save();

            return true;
        } catch (Exception $e) {
            Log::error('OTPRepository::matchOtp', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Deletes the OTP for a specified operation.
     *
     * Removes any active OTPs associated with the given user and operation.
     *
     * @param User $user The user whose OTP will be deleted.
     * @param string $operation The operation associated with the OTP (e.g., 'email' verification).
     */
    public function deleteOtp(User $user, string $operation): void
    {
        try {
            $user->otps()->whereOperation($operation)->delete();
        } catch (Exception $e) {
            Log::error('OTPRepository::deleteOtp', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
