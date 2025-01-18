<?php

namespace App\Http\Requests\API\Auth;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SocialLoginRequest extends FormRequest
{
    use ApiResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'provider' => 'required|in:google,facebook',
        ];
    }


    /**
     * Get custom validation error messages.
     *
     * This method defines custom error messages for validation rules applied 
     * to the incoming request data. The messages correspond to specific 
     * fields like 'token' and 'provider', providing clear and descriptive 
     * feedback when validation fails.
     *
     * @return array The custom error messages for validation failures.
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Token is required',
            'provider.required' => 'Provider is required',
            'provider.in' => 'Invalid provider selected. The available options are Google & Facebook.',
        ];
    }



    /**
     * Handle a failed validation attempt.
     *
     * This method is triggered when the validation fails. It checks if the
     * validation errors for the 'token' or 'provider' fields are present and
     * selects the first error message to return as part of the response.
     * 
     * @param Validator $validator The validator instance containing the validation errors.
     * 
     * @throws ValidationException Throws a validation exception with the custom error response.
     */
    protected function failedValidation(Validator $validator): never
    {
        $tokenErrors = $validator->errors()->get('token') ?? null;
        $providerErrors = $validator->errors()->get('provider') ?? null;

        if ($tokenErrors) {
            $message = $tokenErrors[0];
        } else {
            $message = $providerErrors[0];
        }

        $response = $this->error(
            422,
            $message,
            $validator->errors(),
        );

        throw new ValidationException($validator, $response);

    }
}
