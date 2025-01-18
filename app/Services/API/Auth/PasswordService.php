<?php

namespace App\Services\API\Auth;

use App\Models\User;
use App\Repositories\API\Auth\PasswordRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordService
{
    protected PasswordRepositoryInterface $passwordRepository;


    /**
     * Constructor for initializing the class with the PasswordRepository dependency.
     *
     * @param PasswordRepositoryInterface $passwordRepository The repository used for handling password-related operations such as reset and update.
     */
    public function __construct(PasswordRepositoryInterface $passwordRepository)
    {
        $this->passwordRepository = $passwordRepository;
    }

    /**
     * Changes the user's password based on the provided email and new password.
     *
     * Validates and updates the user's password in the repository. If any errors occur,
     * they are logged and rethrown.
     *
     * @param string $email The user's email address associated with the account.
     * @param string $password The new password to be set for the user.
     *
     * @return bool
     */
    public function changePassword(string $email, string $password):bool
    {
        try {
            return $this->passwordRepository->changePassword($email, $password);
        } catch (Exception $e) {
            Log::error('PasswordService::changePassword', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
