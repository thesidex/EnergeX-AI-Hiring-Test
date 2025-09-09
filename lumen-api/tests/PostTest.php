<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class PostTest extends TestCase
{
    use DatabaseMigrations;

    public function test_posts_index_is_public()
    {
        $this->get('/api/posts')
            ->seeStatusCode(200)
            ->seeJson(); // list (possibly empty) is OK
    }

    public function test_register_login_and_create_post()
    {
        // Register => 201
        $this->json('POST', '/api/register', [
            'name' => 'Tester',
            'email' => 't@example.com',
            'password' => 'secret123'
        ])
        ->seeStatusCode(201)
        ->seeJsonStructure(['id']);

        // Login => 200, get token
        $this->json('POST', '/api/login', [
            'email' => 't@example.com',
            'password' => 'secret123'
        ])
        ->seeStatusCode(200)
        ->seeJsonStructure(['token', 'user' => ['id','name','email']]);

        $login = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('token', $login);
        $token = $login['token'];

        // Create post => 201
        $this->json(
            'POST',
            '/api/posts',
            ['title' => 'Hello', 'content' => 'World'],
            ['Authorization' => 'Bearer ' . $token]
        )
        ->seeStatusCode(201)
        ->seeJsonStructure(['id','title','content','user_id']);

        // DB assertions
        $this->seeInDatabase('users', ['email' => 't@example.com']);
        $this->seeInDatabase('posts', ['title' => 'Hello', 'content' => 'World']);
    }

    public function test_create_post_requires_auth()
    {
        $this->json('POST', '/api/posts', ['title' => 'X', 'content' => 'Y'])
             ->seeStatusCode(401);
    }

    public function test_create_post_validates_title_and_content()
    {
        // Prepare a user + token
        $token = $this->registerAndLogin('u1@example.com');

        // Missing title
        $this->json('POST', '/api/posts', ['content' => 'Body'], ['Authorization' => 'Bearer ' . $token])
             ->seeStatusCode(422);

        // Missing content
        $this->json('POST', '/api/posts', ['title' => 'Title'], ['Authorization' => 'Bearer ' . $token])
             ->seeStatusCode(422);
    }

    public function test_update_and_delete_require_ownership()
    {
        // User A creates a post
        $tokenA = $this->registerAndLogin('a@example.com');
        $this->json('POST', '/api/posts', ['title' => 'T1', 'content' => 'C1'], ['Authorization' => 'Bearer ' . $tokenA])
             ->seeStatusCode(201);
        $post = json_decode($this->response->getContent(), true);

        // User B tries to update/delete A's post -> 403
        $tokenB = $this->registerAndLogin('b@example.com');

        $this->json('PUT', "/api/posts/{$post['id']}", ['title' => 'Hacked', 'content' => 'Nope'], ['Authorization' => 'Bearer ' . $tokenB])
             ->seeStatusCode(403);

        $this->json('DELETE', "/api/posts/{$post['id']}", [], ['Authorization' => 'Bearer ' . $tokenB])
             ->seeStatusCode(403);

        // Owner can update and delete
        $this->json('PUT', "/api/posts/{$post['id']}", ['title' => 'T2', 'content' => 'C2'], ['Authorization' => 'Bearer ' . $tokenA])
             ->seeStatusCode(200)
             ->seeJson(['title' => 'T2', 'content' => 'C2']);

        $this->json('DELETE', "/api/posts/{$post['id']}", [], ['Authorization' => 'Bearer ' . $tokenA])
             ->seeStatusCode(200);

        $this->notSeeInDatabase('posts', ['id' => $post['id']]);
    }

    public function test_show_404_for_missing_post()
    {
        $this->get('/api/posts/999999')
             ->seeStatusCode(404);
    }

    // ---------- helpers ----------

    private function registerAndLogin(string $email): string
    {
        $this->json('POST', '/api/register', [
            'name' => strtok($email, '@'),
            'email' => $email,
            'password' => 'secret123',
        ])->seeStatusCode(201);

        $this->json('POST', '/api/login', [
            'email' => $email,
            'password' => 'secret123',
        ])->seeStatusCode(200);

        $login = json_decode($this->response->getContent(), true);
        return $login['token'];
    }
}
