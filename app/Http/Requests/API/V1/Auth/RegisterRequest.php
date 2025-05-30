<?php

namespace App\Http\Requests\API\V1\Auth;

use App\Traits\V1\ApiResponse;
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
            'name' => "required|string",
            'email'      => "required|email|unique:users",
            'password'   => "required|confirmed",
            'gender' => 'required|in:male,female, others',
            'country' => 'required|string',
            'date_of_birth' => 'required|date',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',
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
            'name.required' => 'Name is required.',
            'name.string'   => 'Name must be a string.',

            'email.required' => 'Email address is required.',
            'email.email'    => 'Email address must be a valid email format.',
            'email.unique'   => 'This email is already taken.',

            'password.required'  => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',

            'gender.required' => 'Gender is required.',
            'gender.in'       => 'Gender must be one of the following: male, female, or others.',

            'country.required' => 'Country is required.',
            'country.string'   => 'Country must be a string.',

            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.date'     => 'Date of birth must be a valid date.',

            'avatar.image'         => 'Avatar must be an image.',
            'avatar.mimes'         => 'Avatar must be a file of type: jpeg, png, jpg, gif, svg, webp.',
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
    protected function failedValidation(Validator $validator): never
    {

        $nameError = $validator->errors()->get('name') ?? null;
        $emailErrors = $validator->errors()->get('email') ?? null;
        $passwordErrors = $validator->errors()->get('password') ?? null;
        $genderErrors = $validator->errors()->get('gender') ?? null;
        $countryErrors = $validator->errors()->get('country') ?? null;
        $dateOfBirthErrors = $validator->errors()->get('date_of_birth') ?? null;
        $avatarErrors = $validator->errors()->get('avatar') ?? null;

        if ($nameError) {
            $message = $nameError[0];
        } else if ($emailErrors) {
            $message = $emailErrors[0];
        } else if ($passwordErrors) {
            $message = $passwordErrors[0];
        } else if ($genderErrors) {
            $message = $genderErrors[0];
        } else if ($countryErrors) {
            $message = $countryErrors[0];
        } else if ($dateOfBirthErrors) {
            $message = $dateOfBirthErrors[0];
        } else if ($avatarErrors) {
            $message = $avatarErrors[0];
        }

        $response = $this->error(
            422,
            $message,
            $validator->errors(),
        );
        throw new ValidationException($validator, $response);
    }
}
