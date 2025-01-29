<?php

namespace App\Services\API\V1\Profile;

use App\Models\User;
use App\Repositories\API\V1\Profile\UserRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected UserRepositoryInterface $userRepositoryInterface;

    public function __construct(UserRepositoryInterface $userRepositoryInterface)
    {
        $this->userRepositoryInterface = $userRepositoryInterface;
    }


    /**
     * Update the user's profile information.
     *
     * @param array $credentials The profile data to update.
     * @return User The updated user instance.
     * @throws Exception If an error occurs during the update process.
     */
    public function updateProfile(array $credentials): Collection|User
    {
        try {
            return $this->userRepositoryInterface->updateUserProfile($credentials);
        } catch (Exception $e) {
            Log::error("UserService:updateProfile", [$e->getMessage()]);
            throw $e;
        }
    }
}
