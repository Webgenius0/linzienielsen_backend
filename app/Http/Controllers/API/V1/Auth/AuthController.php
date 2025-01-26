<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Services\API\V1\Auth\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Auth\LoginRequest;
use App\Http\Requests\API\V1\Auth\RegisterRequest;
use App\Traits\V1\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponse;
    protected AuthService $authService;

    /**
     * Constructor for initializing the class with the AuthService dependency.
     *
     * @param AuthService $authService The authentication service instance used for handling authentication-related operations.
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }



    /**
     * Handles the user registration process by validating the request and delegating
     * the registration logic to the AuthService.
     *
     * This method first validates the incoming registration data using the provided
     * RegisterRequest. If validation passes, it calls the AuthService to register
     * the user and generate a JWT token. Upon success, it returns a success response
     * with the generated token. If an error occurs during the registration process,
     * it returns an error response with the appropriate message.
     *
     * @param RegisterRequest $request The validated registration request containing user data:
     *                                 - 'name' (string): The user's full name.
     *                                 - 'email' (string): The user's email address.
     *                                 - 'password' (string): The user's password.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response with the registration result:
     *                                      - On success: Returns the JWT token and a success message.
     *                                      - On failure: Returns the error message.
     *
     * @throws Exception If any error occurs during user registration.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $response = $this->authService->register($validatedData);

            return $this->success(200, 'Registration Successfull', $response);
        } catch (Exception $e) {
            Log::error('AuthService::register', ['error' => $e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }



    /**
     * Handles the user registration process.
     *
     * Validates the incoming registration request data, calls the AuthService to register the user,
     * and returns a JSON response with the result of the registration attempt.
     *
     * @param RegisterRequest $request The validated registration request containing user data.
     *
     * @return JsonResponse The JSON response indicating the success or failure of the registration.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $response = $this->authService->login($validatedData);

            return $this->success(200, 'Login Successfull', $response);
        } catch (Exception $e) {
            Log::error('AuthService::login', ['error' => $e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }




    /**
     * Handles user logout by terminating the user's session.
     *
     * Calls the AuthService to perform the logout operation, and returns a JSON response
     * indicating the success or failure of the logout attempt.
     *
     * @return JsonResponse The JSON response indicating the success or failure of the logout.
     */
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return $this->success(200, 'Logout Successfull');
        } catch (Exception $e) {
            Log::error('AuthService::logout', ['error' => $e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }




    /**
     * Refreshes the authentication token for the user.
     *
     * Retrieves the current token, refreshes it, and returns a new token in the response.
     * If the refresh operation fails, an error message is logged and a failure response is returned.
     *
     * @return JsonResponse The JSON response containing the updated token or an error message.
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return $this->success(200, 'Token Updated', ['token' => $token]);
        } catch (Exception $e) {
            Log::error('AuthService::refresh', ['error' => $e->getMessage()]);
            return $this->error(500, 'server Error', $e->getMessage());
        }
    }
}
