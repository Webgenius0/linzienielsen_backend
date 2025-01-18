<?php

namespace App\Rules\API\Auth;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class Validatepassword implements ValidationRule
{

    protected $email;
    
    public function __construct(string $email)
    {
        $this->email = $email;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Retrieve the user by email
        $user = User::where('email', $this->email)->first();

        // Check if user exists and if the password matches
        if (!$user || !Hash::check($value, $user->password)) {
            // If password doesn't match, trigger validation failure
            $fail('The provided password is incorrect.');
        }
    }
}
