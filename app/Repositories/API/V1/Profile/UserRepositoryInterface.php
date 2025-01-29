<?php

namespace App\Repositories\API\V1\Profile;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{

    /**
     * Retrieve the authenticated user's basic details along with their profile.
     *
     * This method fetches the authenticated user with selected attributes (`id`, `name`, `avatar`)
     * and includes related profile data (`id`, `user_id`, `gender`, `date_of_birth`, `country`).
     * If the user is not found, it throws an exception.
     */
    public function getAuthUser();

    /**
     * Update the user's profile information, including their name, handle, profile data, and avatar.
     * If the user profile does not exist, it will be created.
     *
     * @param array $credentials The profile data to update (name, handle, gender, country, date_of_birth, avatar).
     * @return User The updated user instance.
     */
    public function updateUserProfile(array $credentials);
}
