<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnsureGuestJwt
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     * Check if the user is authenticated via JWT
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            $user = null;
        }

        if ($user) {
            return $this->error(403, 'already authenticated', []);
        }

        return $next($request);
    }
}
