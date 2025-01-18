<?php

namespace App\Repositories\API\Auth;

use App\Models\User;

interface OTPRepositoryInterface
{
    /**
     * Sends an OTP to the user for a specified operation.
     *
     * @param User $user The user to send the OTP to.
     * @param string $operation The operation for which the OTP is generated.
     *
     * @return int The generated OTP.
     */
    public function sendOtp(User $user, string $operation):int;

    /**
     * Validates the OTP for a specified operation.
     *
     * @param User $user The user whose OTP is being validated.
     * @param string $operation The operation associated with the OTP.
     * @param string $otp The OTP to validate.
     *
     * @return bool True if the OTP is valid, false otherwise.
     */
    public function matchOtp(User $user, string $operation, string $otp):bool;

    /**
     * Deletes the OTP for a specified operation.
     *
     * @param User $user The user whose OTP is to be deleted.
     * @param string $operation The operation associated with the OTP.
     */
    public function deleteOtp(User $user, string $operation):void;
}
