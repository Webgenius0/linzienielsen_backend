<?php

namespace App\Exceptions;

use Exception;

class OTPExpiredException extends Exception
{
    protected $message = 'OTP Has Expired';
    protected $code = 400; // HTTP status code
}
