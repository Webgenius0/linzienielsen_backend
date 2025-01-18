<?php

namespace App\Services\API\Auth;

use App\Exceptions\SocialLoginException;
use App\Models\User;
use App\Repositories\API\Auth\SocialLoginRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialLoginService
{
    protected $socialLoginRepository;


    /**
     * Constructor for initializing the class with the SocialLoginRepository dependency.
     *
     * @param SocialLoginRepositoryInterface $socialLoginRepository The repository used for handling social login data and operations.
     */
    public function __construct(SocialLoginRepositoryInterface $socialLoginRepository)
    {
        $this->socialLoginRepository = $socialLoginRepository;
    }

    /**
     * Handles the social login process for a user via a third-party provider.
     *
     * Validates the provided social login token, retrieves user information from the social provider,
     * and either logs in an existing user or creates a new user if they don't exist.
     * It also handles errors such as invalid tokens, deleted accounts, and logs the user in upon successful authentication.
     *
     * @param array $credentials The social login credentials including the provider and token.
     *
     * @return mixed The result of the login operation, as handled by the repository.
     *
     * @throws SocialLoginException If the token is invalid, the account is deleted, or other social login errors occur.
     */
    public function handleSocialLogin(array $credentials)
    {
        try {
            // Get user info from the social login provider
            $socialUser = Socialite::driver($credentials['provider'])->stateless()->userFromToken($credentials['token']);

            if (!$socialUser) {
                throw new SocialLoginException("Invalid social login token or provider.", 401);
            }

            // Find the user by email
            $user = $this->socialLoginRepository->findByEmail($socialUser->getEmail());

            if ($user && !empty($user->deleted_at)) {
                throw new SocialLoginException("Your account has been deleted.", 410);
            }

            if (!$user) {
                // Generate a random password for new user
                $password = Str::random(8);

                $name = $socialUser->getName();
                $nameParts = explode(' ', $name, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                // Create the new user
                $newUser = $this->socialLoginRepository->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $socialUser->getEmail(),
                    'password' => $password,
                    'address' => null,
                ]);

                // Log the user in
                return $this->socialLoginRepository->login(['email' => $newUser->email]);
            }

            // If user exists, login logic
            return $this->socialLoginRepository->login(['email' => $socialUser->getEmail()]);
        } catch (SocialLoginException $e) {
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SocialLoginService::handleSocialLogin', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
