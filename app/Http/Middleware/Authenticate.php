<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;
use Laravel\Sanctum\PersonalAccessToken;

class Authenticate extends Middleware
{


    public function handle($request, Closure $next, ...$guards)
    {


        if (!Auth::guard('api')->check()) {
            return response()->json(['message' => 'Token not provided'], 401);
        } else {

            return $next($request);
        }

    }
}
