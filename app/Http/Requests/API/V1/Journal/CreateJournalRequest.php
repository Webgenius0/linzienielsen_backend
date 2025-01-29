<?php

namespace App\Http\Requests\API\V1\Journal;

use App\Traits\V1\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class CreateJournalRequest extends FormRequest
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
            'title' => 'required|string',
            'content' => 'required|string',
            'reminder_type' => 'required|in:daily,weekly,monthly',
            'reminder_time' => 'required|date_format:H:i',
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
            'title.required' => 'The note title is required.',
            'title.string' => 'The title must be a valid string.',
            'content.required' => 'The content field cannot be empty.',
            'content.string' => 'The content must be a valid string.',
            'reminder_type.required' => 'Please select a reminder type (daily, weekly, or monthly).',
            'reminder_type.in' => 'Invalid reminder type. Choose from daily, weekly, or monthly.',
            'reminder_time.required' => 'Please specify a reminder time.',
            'reminder_time.date_format' => 'Invalid format for reminder time. Please use HH:MM format.',
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
        $fields = ['title', 'content', 'reminder_type', 'reminder_time'];

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
