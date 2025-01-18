<?php

namespace App\Http\Requests\API\Auth;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
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
            'first_name' => "required|string",
            'last_name'  => "required|string",
            'email'      => "required|email|unique:users",
            'password'   => "required|confirmed",
        ];
    }

    /**
     * Define the custom validation error messages.
     *
     * @return array The custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.string'   => 'First name must be a string.',

            'last_name.required' => 'Last name is required.',
            'last_name.string'   => 'Last name must be a string.',

            'email.required' => 'Email address is required.',
            'email.email'    => 'Email address must be a valid email format.',
            'email.unique'   => 'This email is already taken.',

            'password.required'  => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }



    /**
     * Handles failed validation by formatting the validation errors and throwing a ValidationException.
     * 
     * This method is called when validation fails in a form request. It uses the `error` method 
     * from the `ApiResponse` trait to generate a standardized Errorsresponse with the validation 
     * Errorsmessages and a 422 HTTP status code. It then throws a `ValidationException` with the 
     * formatted response.
     *
     * @param Validator $validator The validator instance containing the validation errors.
     *
     * @return void Throws a ValidationException with a formatted Errorsresponse.
     * 
     * @throws ValidationException The exception is thrown to halt further processing and return validation errors.
     */
    protected function failedValidation(Validator $validator):never
    {

        $firstNameErrors = $validator->errors()->get('first_name') ?? null;
        $lastNameErrors = $validator->errors()->get('last_name') ?? null;
        $emailErrors = $validator->errors()->get('email') ?? null;
        $passwordErrors = $validator->errors()->get('password') ?? null;

        if ($firstNameErrors) {
            $message = $firstNameErrors[0];
        } else if ($lastNameErrors) {
            $message = $lastNameErrors[0];
        } else if ($emailErrors) {
            $message = $emailErrors[0];
        } else if ($passwordErrors) {
            $message = $passwordErrors[0];
        }

        $response = $this->error(
            422,
            $message,
            $validator->errors(),
        );
        throw new ValidationException($validator, $response);
    }
}
