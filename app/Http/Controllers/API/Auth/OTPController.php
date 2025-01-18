<?php

namespace App\Http\Controllers\API\Auth;

use App\Exceptions\OTPExpiredException;
use App\Exceptions\OTPMismatchException;
use App\Exceptions\UserAlreadyVarifiedException;
use App\Http\Requests\API\Auth\OTPRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\OTPMatchRequest;
use App\Services\API\Auth\OTPService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OTPController extends Controller
{
    use ApiResponse;

    protected OTPService $otpService;

    /**
     * Constructor for initializing the class with the OTPService dependency.
     *
     * @param OTPService $otpService The service used for handling OTP (One-Time Password) generation and validation.
     */
    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }




    /**
     * Sends an OTP (One-Time Password) to the provided email address.
     *
     * Takes the email and operation details from the request, generates the OTP using the OTPService,
     * and returns a JSON response with the OTP or an error message.
     *
     * @param OTPRequest $request The request containing the email and operation for which the OTP is being generated.
     *
     * @return JsonResponse The JSON response with the OTP or an error message.
     */
    public function otpSend(OTPRequest $request): JsonResponse
    {
        try {
            $otp = $this->otpService->sendOtp($request->email, $request->operation);
            return $this->success(200, 'OTP Sended');
        } catch (Exception $e) {
            Log::error('OTPController::otpSend', ['error' => $e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }



    /**
     * Verifies the provided OTP (One-Time Password) for the given email and operation.
     *
     * Takes the email, operation, and OTP from the request, validates the OTP using the OTPService,
     * and returns a JSON response indicating whether the OTP is verified or an error message.
     *
     * @param OTPMatchRequest $request The request containing the email, operation, and OTP to be verified.
     *
     * @return JsonResponse The JSON response indicating the OTP verification result or an error message.
     */
    public function otpMatch(OTPMatchRequest $request): JsonResponse
    {
        try {
            $response = $this->otpService->matchOtp($request->email, $request->operation, $request->otp);
            if ($response) {
                return $this->success(200, 'OTP Verified', $response);
            }
            throw new Exception('Server Error', 500);
        } catch (ModelNotFoundException $e) {
            Log::error('User Not Found: ' . $e->getMessage());
            return $this->error($e->getCode(), 'User Not Found', $e->getMessage());
        } catch (UserAlreadyVarifiedException $e) {
            return $this->error($e->getCode(), 'User is already verified', $e->getMessage());
        } catch (OTPMismatchException $e) {
            return $this->error($e->getCode(), 'OTP did not match', $e->getMessage());
        } catch (OTPExpiredException $e) {
            return $this->error($e->getCode(), 'OTP Expired', $e->getMessage());
        } catch (Exception $e) {
            Log::error('OTPController::otpMatch', ['error' => $e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }
}
