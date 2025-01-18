<?php

namespace App\Exceptions;

use Exception;

class OTPNotVerifiedException extends Exception
{
    protected $message = 'OTP Not Verified';
    protected $code = 404;
}
