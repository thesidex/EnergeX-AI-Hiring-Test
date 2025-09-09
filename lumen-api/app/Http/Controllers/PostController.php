<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class PostController extends BaseController
{
    /** Seconds to keep items in cache (override via CACHE_TTL in .env) */
    private int $ttl;

    public function __construct()
    {
        $this->ttl = (int) env('CACHE_TTL', 60); // default: 60s
    }

    /**
     * GET /api/posts
     * Cached list of posts (desc id)
     */
    public function index()
    {
        try {
            $posts = Cache::remember('posts_all', $this->ttl, function () {
                return Post::orderBy('id', 'desc')->get();
            });

            return response()->json($posts, 200);
        } catch (\Throwable $e) {
            Log::error('Posts index error', ['ex' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * GET /api/posts/{id}
     * Cached single post
     */
    public function show($id)
    {
        try {
            $cacheKey = $this->postKey($id);

            $post = Cache::remember($cacheKey, $this->ttl, function () use ($id) {
                return Post::find($id);
            });

            if (!$post) {
                return response()->json(['error' => 'Not found'], 404);
            }

            return response()->json($post, 200);
        } catch (\Throwable $e) {
            Log::error('Posts show error', ['ex' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * POST /api/posts
     * Create a new post (auth required)
     */
    public function store(Request $request)
    {
        try {
            $user = $request->attributes->get('user');
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Read inputs in a way that works for ALL content types (JSON or form)
            $title   = trim((string) $request->input('title', ''));
            $content = trim((string) $request->input('content', ''));

            // Validate without relying on global handler
            $v = \Illuminate\Support\Facades\Validator::make(
                ['title' => $title, 'content' => $content],
                ['title' => 'required|string', 'content' => 'required|string']
            );

            if ($v->fails()) {
                return response()->json([
                    'error'  => 'Validation failed',
                    'fields' => $v->errors(),
                ], 422);
            }

            $post = Post::create([
                'title'   => $title,
                'content' => $content,
                'user_id' => $user->id,
            ]);

            // Invalidate list cache; optionally warm item cache
            \Illuminate\Support\Facades\Cache::forget('posts_all');
            \Illuminate\Support\Facades\Cache::put($this->postKey($post->id), $post, $this->ttl);

            return response()->json($post, 201);
        } catch (\Throwable $e) {
            \Log::error('Posts store error', ['ex' => $e->getMessage()]);
            if (env('APP_DEBUG')) {
                return response()->json(['error' => 'Server error', 'reason' => $e->getMessage()], 500);
            }
            return response()->json(['error' => 'Server error'], 500);
        }

    }

    /**
     * PUT/PATCH /api/posts/{id}
     * Update a post you own (auth required)
     */
    public function update($id, Request $request)
    {
        try {
            $user = $request->attributes->get('user');
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Not found'], 404);
            }

            if ((int) $post->user_id !== (int) $user->id) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $title   = trim((string) $request->input('title', $post->title));
            $content = trim((string) $request->input('content', $post->content));

            if ($title === '' || $content === '') {
                return response()->json(['error' => 'title and content are required'], 422);
            }

            $post->title   = $title;
            $post->content = $content;
            $post->save();

            // Invalidate caches
            Cache::forget('posts_all');
            Cache::forget($this->postKey($id));

            return response()->json($post, 200);
        } catch (\Throwable $e) {
            Log::error('Posts update error', ['ex' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * DELETE /api/posts/{id}
     * Delete a post you own (auth required)
     */
    public function destroy($id, Request $request)
    {
        try {
            $user = $request->attributes->get('user');
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Not found'], 404);
            }

            if ((int) $post->user_id !== (int) $user->id) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $post->delete();

            // Invalidate caches
            Cache::forget('posts_all');
            Cache::forget($this->postKey($id));

            return response()->json(['ok' => true], 200);
        } catch (\Throwable $e) {
            Log::error('Posts destroy error', ['ex' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    private function postKey($id): string
    {
        return "post_{$id}";
    }
}
