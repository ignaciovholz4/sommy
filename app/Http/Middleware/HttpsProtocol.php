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

        // TrustProxies handles X-Forwarded-Proto, so $request->secure() is reliable behind the LB.
        if (!$request->secure()) {
            // GET/HEAD can be redirected safely (no body to lose).
            if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
                return redirect()->secure($request->getRequestUri());
            }

            // A POST/PUT/PATCH/DELETE over plain HTTP would send credentials/data
            // unencrypted. Reject it instead of forwarding it in the clear.
            abort(400, 'HTTPS is required.');
        }

        return $next($request);
    }
}
