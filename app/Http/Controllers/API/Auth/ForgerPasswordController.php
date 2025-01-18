<?php

namespace App\Http\Controllers\API\Auth;

use App\Exceptions\OTPNotVerifiedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ForgetPasswordResetRequest;
use App\Services\API\Auth\ForgerPasswordService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ForgerPasswordController extends Controller
{
    use ApiResponse;

    protected ForgerPasswordService $forgerPasswordService;


    /**
     * Constructor for initializing the class with the ForgerPasswordService dependency.
     *
     * @param ForgerPasswordService $forgerPasswordService The service used for handling password recovery and management.
     */
    public function __construct(ForgerPasswordService $forgerPasswordService)
    {
        $this->forgerPasswordService = $forgerPasswordService;
    }


    /**
     * Resets the user's password based on the provided reset request data.
     *
     * Validates the incoming password reset request, processes the reset via the ForgerPasswordService,
     * and returns a JSON response indicating the success or failure of the password reset operation.
     *
     * @param ForgetPasswordResetRequest $forgetPasswordResetRequest The validated request containing the reset data.
     *
     * @return JsonResponse The JSON response with the result of the password reset process.
     */
    public function resetPassword(ForgetPasswordResetRequest $forgetPasswordResetRequest): JsonResponse
    {
        try {
            $validatedData = $forgetPasswordResetRequest->validated();
            $response = $this->forgerPasswordService->resetPassword($validatedData);
            if ($response) {
                return $this->success(200, 'Password Reset Successfull');
            }
            throw new Exception('Server Error', 500);
        } catch (ModelNotFoundException $e) {
            return $this->error(500, 'User Not Found', $e->getMessage());
        } catch (OTPNotVerifiedException $e) {
            return $this->error(500, 'OTP Not Verified', $e->getMessage());
        } catch (Exception $e) {
            Log::error('ForgerPasswordController::resetPassword', ['error' => $e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }
}
