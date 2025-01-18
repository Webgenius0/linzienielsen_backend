<?php

namespace App\Exceptions;

use Exception;

class OTPMismatchException extends Exception
{
    protected $message = 'OTP Did Not Match';
    protected $code = 400;
}
