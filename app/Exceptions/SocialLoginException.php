<?php

namespace App\Exceptions;

use Exception;

class SocialLoginException extends Exception
{
    protected $message;
    protected $code;

    /**
     * SocialLoginException constructor.
     * 
     * Initializes a new instance of the SocialLoginException with a custom message and code.
     * 
     * @param string $message The error message describing the problem.
     * @param int $code The error code to categorize the exception.
     */
    public function __construct($message, $code)
    {
        $this->message = $message;
        $this->code = $code;
    }

}
