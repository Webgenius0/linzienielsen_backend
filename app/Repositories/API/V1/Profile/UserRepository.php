<?php

namespace App\Repositories\API\V1\Profile;

use App\Helpers\Helper;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
    private $user;
    public function __construct()
    {
        $this->user = Auth::user();
    }


    public function getAuthUser(): Collection
    {
        try {
            $user = User::select('name', 'avatar')->with([
                'profile' => function ($query) {
                    $query->select('gender', 'date_of_birth', 'country', 'user_id');  // Select specific columns from Profile
                }
            ])->findOrFail($this->user->id);
            
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("UserRepository::getAuthUser", [$e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update the user's profile information, including their name, handle, profile data, and avatar.
     * If the user profile does not exist, it will be created.
     *
     * @param array $credentials The profile data to update (name, handle, gender, country, date_of_birth, avatar).
     * @return User The updated user instance.
     * @throws Exception If an error occurs during the update process, the transaction is rolled back.
     */
    public function updateUserProfile(array $credentials): Collection
    {
        try {
            $user = User::findOrFail($this->user->id);
            $user->load('profile');

            DB::beginTransaction();
            if ($user->name !== $credentials['name']) {
                $user->update([
                    'name' => $credentials['name'],
                    'handle' => Helper::generateUniqueSlug($credentials['name'], 'users', 'handle'),
                ]);
            }

            $user->profile()->update([
                'gender' => $credentials['gender'],
                'country' => $credentials['country'],
                'date_of_birth' => $credentials['date_of_birth'],
            ]);

            if (isset($credentials['avatar'])) {
                $oldAvatar = $user->avatar;
                $url = Helper::uploadFile($credentials['avatar'], 'user/' . $user->id);
                if ($url) {
                    $user->update(['avatar' => $url]);
                    Helper::deleteFile($oldAvatar);
                }
            }

            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("UserRepository::updateUserProfile", [$e->getMessage()]);
            throw $e;
        }
    }
}
