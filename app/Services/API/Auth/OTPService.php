<?php

namespace App\Services\API\Auth;

use App\Exceptions\OTPExpiredException;
use App\Exceptions\OTPMismatchException;
use App\Exceptions\UserAlreadyVarifiedException;
use App\Jobs\SendOTPEmail;
use App\Repositories\API\Auth\OTPRepositoryInterface;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OTPService
{
    protected OTPRepositoryInterface $otpRepository;


    /**
     * Constructor for initializing the class with the OTPRepository dependency.
     *
     * @param OTPRepositoryInterface $otpRepository The repository used for handling OTP-related data and operations.
     */
    public function __construct(OTPRepositoryInterface $otpRepository)
    {
        $this->otpRepository = $otpRepository;
    }


    /**
     * Sends an OTP (One-Time Password) to the specified user's email for a given operation.
     *
     * Retrieves the user by email, generates an OTP through the OTPRepository, and returns the OTP.
     * If the user is not found, a ModelNotFoundException is thrown. Other errors are logged and rethrown.
     *
     * @param string $email The user's email address.
     * @param string $operation The operation for which the OTP is being generated (e.g., password reset, login).
     *
     * @return int The generated OTP.
     */
    public function sendOtp(string $email, string $operation): int
    {
        try {
            // Retrieve user by email
            $user = User::whereEmail($email)->first();
            if (!$user) {
                throw new ModelNotFoundException('User not found', 404);
            }
            // Generate and save OTP
            $otp = $this->otpRepository->sendOtp($user, $operation);
            return $otp;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('OTPService::sendOtp', ['error' => $e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Matches the provided OTP with the user's record for a given operation.
     *
     * Retrieves the user by email and validates the OTP for the specified operation (e.g., email verification).
     * If the OTP is valid, it updates the user's record (e.g., marking email as verified) and returns the user's role.
     * Throws exceptions for user not found, already verified users, OTP mismatches, and expired OTPs.
     *
     * @param string $email The user's email address.
     * @param string $operation The operation requiring OTP verification (e.g., 'email').
     * @param string $otp The OTP to validate.
     *
     * @return ?array The user's role if OTP is valid and the operation is successful, or null if invalid.
     */
    public function matchOtp(string $email, string $operation, string $otp): ?array
    {
        try {
            // Retrieve user by email
            $user = User::whereEmail($email)->first();
            if (!$user) {
                throw new ModelNotFoundException('User Not Found', 404);
            }

            if ($user->email_verified_at && $operation == 'email') {
                throw new UserAlreadyVarifiedException();
            }

            // Match OTP using the repository
            $isValid = $this->otpRepository->matchOtp($user, $operation, $otp);

            if ($isValid) {
                DB::beginTransaction();

                // Perform operation-specific logic
                if ($operation === 'email') {
                    $user->email_verified_at = now();
                    $user->save();
                }

                DB::commit();
                return ['role' => $user->role];
            }

            return null;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (UserAlreadyVarifiedException $e) {
            throw $e;
        } catch (OTPMismatchException $e) {
            DB::rollBack();
            throw $e;
        } catch (OTPExpiredException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('OTPService::matchOtp', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
