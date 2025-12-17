<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id', 'content', 'parent_id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     // komentar induk
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // balasan
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
                    ->with('user')
                    ->orderBy('created_at', 'asc');
    }

    public function likes()
{
    return $this->belongsToMany(
        User::class,
        'comment_likes'
    )->withTimestamps();
}

}
