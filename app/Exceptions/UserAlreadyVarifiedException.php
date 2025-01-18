<?php

namespace App\Exceptions;

use Exception;

class UserAlreadyVarifiedException extends Exception
{
    protected $message = 'User is Already Verified';
    protected $code = 400;
}
