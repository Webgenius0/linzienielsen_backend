<?php

namespace App\Http\Controllers\API\V1\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\API\V1\Profile\ProfileResource;
use App\Services\API\V1\Profile\UserService;
use App\Traits\V1\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ApiResponse;
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function update(UpdateProfileRequest $updateProfileRequest):JsonResponse
    {
        try {
            $validatedData = $updateProfileRequest->validated();
            $response = $this->userService->updateProfile($validatedData);
            return $this->success(200, "Profile Updated", new ProfileResource($response));
        }catch(Exception $e) {
            Log::error("UserController::update", [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }
}
