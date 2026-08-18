<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HttpsProtocol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip HTTPS redirect on localhost (HTTP-only environments)
        if (app()->environment('local')) {
            return $next($request);
        }

        // Only redirect GET/HEAD requests to HTTPS; POST/PUT/DELETE/etc pass through
        // to avoid losing request body data. TrustProxies handles X-Forwarded-Proto.
        if (!$request->secure() && $request->isMethod('GET')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
