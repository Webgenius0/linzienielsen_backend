<?php

namespace App\Http\Requests\API\V1\Profile;

use App\Traits\V1\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',
            'gender' => 'nullable|in:male,female, others',
            'country' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
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
            'name.string' => 'The name must be a valid string.',

            'avatar.mimes' => 'The avatar must be a file of type: jpeg, png, jpg, gif, svg, webp.',

            'gender.in' => 'Please select a valid gender (male, female, or others).',

            'country.string' => 'The country must be a valid string.',

            'date_of_birth.date' => 'Please enter a valid date for the date of birth.',
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
        $errors = $validator->errors()->getMessages();
        $message = null;
        $fields = ['name', 'avatar', 'gender', 'country', 'date_of_birth'];

        foreach ($fields as $field) {
            if (isset($errors[$field])) {
                $message = $errors[$field][0];
                break;
            }
        }

        $response = $this->error(
            422,
            $message,
            $validator->errors(),
        );
        throw new ValidationException($validator, $response);
    }
}
