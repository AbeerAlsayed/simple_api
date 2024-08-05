<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ActivationRequestLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    private $rateLimiter;

    public function __construct(RateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    public function handle(Request $request, Closure $next): Response
    {

        $ip = request()->ip();
        $key = "verification-code-request:{$ip}";

        if ($this->rateLimiter->tooManyAttempts($key, 2)){
            return response()->json(['error' => 'You have exceeded the maximum number of activation code requests in a minute.'], 429);
        }

        Cache::put($key, time(), 2);

        return $next($request);
    }
}
