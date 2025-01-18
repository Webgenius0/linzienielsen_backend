<?php

namespace App\Http\Controllers\API\Auth;

use App\Exceptions\SocialLoginException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\SocialLoginRequest;
use App\Services\API\Auth\SocialLoginService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SocialLoginController extends Controller
{
    use ApiResponse;
    protected $socialLoginService;

    /**
     * Constructor for initializing the class with the SocialLoginService dependency.
     *
     * @param SocialLoginService $socialLoginService The service used for handling social login integrations.
     */
    public function __construct(SocialLoginService $socialLoginService)
    {
        $this->socialLoginService = $socialLoginService;
    }


    /**
     * Handles the social login process for the user.
     *
     * Validates the incoming social login request data, processes the login through the SocialLoginService,
     * and returns a JSON response with the authentication token or an error message.
     *
     * @param SocialLoginRequest $socialLoginRequest The request containing the necessary data for social login.
     *
     * @return JsonResponse The JSON response indicating the success or failure of the social login process.
     */
    public function socialLogin(SocialLoginRequest $socialLoginRequest):JsonResponse
    {
        try {
            $validatedData = $socialLoginRequest->validated();
            $token = $this->socialLoginService->handleSocialLogin($validatedData);
            return $this->success(200, 'SocilLogin Successfull', ['token' => $token]);
        } catch (SocialLoginException $e) {
            Log::error('SocialLoginController::socialLogin', ['error' => $e->getMessage()]);
            return $this->error($e->getCode(),  $e->getMessage());
        } catch (Exception $e) {
            Log::error('SocialLoginController::socialLogin', ['error' => $e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }
}
