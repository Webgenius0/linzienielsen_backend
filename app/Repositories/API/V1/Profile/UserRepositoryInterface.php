<?php

namespace App\Repositories\API\V1\Profile;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{


    public function getAuthUser():Collection;

    /**
     * Update the user's profile information, including their name, handle, profile data, and avatar.
     * If the user profile does not exist, it will be created.
     *
     * @param array $credentials The profile data to update (name, handle, gender, country, date_of_birth, avatar).
     * @return User The updated user instance.
     */
    public function updateUserProfile(array $credentials):Collection;

}
