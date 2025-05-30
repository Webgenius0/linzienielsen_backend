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
     * Retrieve the authenticated user's profile.
     *
     * This method fetches the currently authenticated user using the
     * UserRepositoryInterface. If an exception occurs, it is logged
     * before being rethrown.
     *
     * @return User|null The authenticated user's profile or null if not found.
     * @throws Exception If an error occurs during retrieval.
     */
    public function getProfile()
    {
        try {
            return $this->userRepositoryInterface->getAuthUser();
        } catch (Exception $e) {
            Log::error("UserService:updateProfile", [$e->getMessage()]);
            throw $e;
        }
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
