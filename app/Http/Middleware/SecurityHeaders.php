<?php
namespace App\Http\Middleware;
use Closure;
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        // Existing security headers
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Loop 2.6 — CSP (Report-Only, 2-week observation window before enforcing)
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com https://maxcdn.bootstrapcdn.com https://embed.tawk.to https://sdk.mercadopago.com https://source.zoom.us https://www.facebook.com; "
             . "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://maxcdn.bootstrapcdn.com; "
             . "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://maxcdn.bootstrapcdn.com; "
             . "img-src 'self' data: https: ; "
             . "frame-src 'self' https://accept.paymob.com https://api.lyra.com https://developers.zoom.us https://meet.jit.si https://sdk.mercadopago.com https://www.facebook.com; "
             . "connect-src 'self' https://embed.tawk.to https://api.lyra.com https://sdk.mercadopago.com; "
             . "object-src 'none'; "
             . "base-uri 'self'; "
             . "form-action 'self' https://accept.paymob.com https://api.lyra.com https://sdk.mercadopago.com; "
             . "report-uri /csp-report; ";
        $response->headers->set('Content-Security-Policy-Report-Only', $csp);

        // Loop 2.6 — Permissions-Policy (enforced immediately, low-risk)
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=(self), payment=(self)');

        return $response;
    }
}
