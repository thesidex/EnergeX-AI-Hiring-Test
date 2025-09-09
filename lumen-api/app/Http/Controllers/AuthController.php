<?php
namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    private function input(Request $req): array
    {
        $json = $req->json()->all();
        return (is_array($json) && !empty($json)) ? $json : $req->all();
    }

    public function register(Request $req)
    {
        $data = $this->input($req);

        // normalize
        $data['name']  = trim((string)($data['name']  ?? ''));
        $data['email'] = strtolower(trim((string)($data['email'] ?? '')));
        $data['password'] = (string)($data['password'] ?? '');

        // NOTE: remove ",dns" to avoid network-dependent failures in tests
        $validator = app('validator')->make($data, [
            'name'     => 'required|string|min:1|max:100', 
            'email'    => 'required|email:rfc|max:150|unique:users,email',
            'password' => 'required|string|min:8|max:128',
        ]);

        if ($validator->fails()) {
            // Helpful while testing
            $payload = ['error' => 'Invalid payload'];
            if (env('APP_DEBUG')) $payload['details'] = $validator->errors();
            return response()->json($payload, 422);
        }

        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'user',
            ]);

            return response()->json(['id' => $user->id], 201);
        } catch (\Throwable $e) {
            Log::error('register_failed', ['ex' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function login(Request $req)
    {
        $data = $this->input($req);

        $email = strtolower(trim((string)($data['email'] ?? '')));
        $pass  = (string)($data['password'] ?? '');

        $validator = app('validator')->make(
            ['email' => $email, 'password' => $pass],
            [
                'email'    => 'required|email:rfc|max:150',
                'password' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            $payload = ['error' => 'Invalid credentials'];
            if (env('APP_DEBUG')) $payload['details'] = $validator->errors();
            return response()->json($payload, 422);
        }

        try {
            $user = User::where('email', $email)->first();
            if (!$user || !Hash::check($pass, $user->password)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $now = time();
            $ttl = (int) env('JWT_TTL', 3600);

            $payload = [
                'iss' => (string) env('JWT_ISS', 'energeX-api'),
                'aud' => (string) env('JWT_AUD', 'energeX-clients'),
                'iat' => $now,
                'nbf' => $now,
                'exp' => $now + $ttl,
                'sub' => $user->id,
            ];

            $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

            return response()->json([
                'token' => $token,
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('login_failed', ['ex' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
