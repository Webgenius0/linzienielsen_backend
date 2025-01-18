<?php

namespace App\Services\API\Auth;

use App\Exceptions\OtpNotVerifiedException;
use App\Models\User;
use App\Repositories\API\Auth\ForgetPasswordRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgerPasswordService
{

    protected ForgetPasswordRepositoryInterface $forgetPasswordRepository;


    /**
     * Constructor for initializing the class with the ForgetPasswordRepository dependency.
     *
     * @param ForgetPasswordRepositoryInterface $forgetPasswordRepository The repository used for handling password reset-related operations.
     */
    public function __construct(ForgetPasswordRepositoryInterface $forgetPasswordRepository)
    {
        $this->forgetPasswordRepository = $forgetPasswordRepository;
    }



    /**
     * Resets the user's password based on the provided credentials.
     *
     * Validates the credentials and attempts to reset the user's password using the ForgetPasswordRepository.
     * If any exceptions occur (such as user not found or OTP not verified), they are rethrown.
     * Any other errors are logged before being thrown.
     *
     * @param array $credentials The user's credentials including email, password, and OTP details.
     *
     * @return bool True if the password was successfully reset, otherwise throws an exception.
     */
    public function resetPassword(array $credentials): bool
    {
        try {
            return $this->forgetPasswordRepository->resetPassword($credentials);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (OtpNotVerifiedException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('ForgerPasswordService::resetPassword', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
