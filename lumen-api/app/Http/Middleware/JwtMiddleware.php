<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Throwable;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $auth = $request->header('Authorization', '');
        if (!preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return response()->json(['error' => 'Missing Bearer token'], 401);
        }

        $token = trim($m[1]);

        try {
            JWT::$leeway = (int) env('JWT_LEEWAY', 60);

            $payload = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

            // Optional claim checks (defense-in-depth)
            $expectedIss = env('JWT_ISS');
            $expectedAud = env('JWT_AUD');

            if ($expectedIss && (!isset($payload->iss) || $payload->iss !== $expectedIss)) {
                return response()->json(['error' => 'Invalid token issuer'], 401);
            }
            if ($expectedAud && (!isset($payload->aud) || $payload->aud !== $expectedAud)) {
                return response()->json(['error' => 'Invalid token audience'], 401);
            }

            if (!isset($payload->sub)) {
                return response()->json(['error' => 'Invalid token subject'], 401);
            }

            $userId = (int) $payload->sub;
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'Invalid token subject'], 401);
            }

            $request->attributes->set('user', $user);

            return $next($request);

        } catch (Throwable $e) {
            $body = ['error' => 'Invalid token'];
            if (env('APP_DEBUG', false)) {
                $body['reason'] = $e->getMessage();
            }
            return response()->json($body, 401);
        }
    }
}
