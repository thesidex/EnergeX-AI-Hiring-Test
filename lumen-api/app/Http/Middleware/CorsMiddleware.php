<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CorsMiddleware
{
    /** @var string[] */
    private array $allowedOrigins;
    private string $allowMethods = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
    private string $allowHeaders = 'Authorization, Content-Type';
    private string $exposeHeaders = ''; // e.g. 'X-Total-Count'
    private bool $allowCredentials;
    private int $maxAge = 600; // seconds

    public function __construct()
    {
        // Comma-separated list in .env, e.g.:
        // CORS_ALLOWED_ORIGINS=http://localhost:5173,https://app.example.com
        $csv = env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173');
        $this->allowedOrigins = array_filter(array_map('trim', explode(',', $csv)));
        $this->allowCredentials = (bool) env('CORS_ALLOW_CREDENTIALS', false);
        $this->exposeHeaders = trim((string) env('CORS_EXPOSE_HEADERS', '')); // optional
    }

    public function handle(Request $request, Closure $next)
    {
        $origin = $request->headers->get('Origin', '');
        $allowedOrigin = $this->resolveOrigin($origin);

        // Preflight
        if ($request->getMethod() === 'OPTIONS') {
            $resp = response('', 204);
            return $this->applyCorsHeaders($resp, $allowedOrigin);
        }

        $response = $next($request);

        if (!$response instanceof SymfonyResponse) {
            $response = response($response);
        }

        return $this->applyCorsHeaders($response, $allowedOrigin);
    }

    private function resolveOrigin(string $origin): string
    {
        if ($origin && in_array($origin, $this->allowedOrigins, true)) {
            return $origin;
        }
        // If you truly want to reflect none, return empty string (no CORS)
        // Or choose a default dev origin:
        return $this->allowedOrigins[0] ?? '';
    }

    private function applyCorsHeaders(SymfonyResponse $response, string $origin): SymfonyResponse
    {
        if ($origin !== '') {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Vary', 'Origin');
            $response->headers->set('Access-Control-Allow-Methods', $this->allowMethods);
            $response->headers->set('Access-Control-Allow-Headers', $this->allowHeaders);
            if ($this->exposeHeaders !== '') {
                $response->headers->set('Access-Control-Expose-Headers', $this->exposeHeaders);
            }
            $response->headers->set('Access-Control-Max-Age', (string) $this->maxAge);
            if ($this->allowCredentials) {
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            }
        }
        return $response;
    }
}
