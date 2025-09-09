<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Table defaults to 'users'
    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden   = ['password'];

    // If you want created_at/updated_at on users, set to true (default).
    // For a leaner schema you can leave timestamps off; the assessment
    // only requires posts.created_at.
    public $timestamps = false;

    protected $casts = [
        'id' => 'integer',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}
