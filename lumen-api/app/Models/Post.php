<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes; // optional

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'user_id'];

    // The assessment requires a created_at column. If you don't want updated_at,
    // keep timestamps on but rename constants so only created_at is tracked.
    public $timestamps = true;
    const UPDATED_AT = null; // only created_at will be managed

    protected $casts = [
        'id'      => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime', // optional but handy
    ];

    // use SoftDeletes; // if you enable soft deletes and add deleted_at column

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
