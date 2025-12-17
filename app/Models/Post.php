<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_url',
        'description',
        'category_id',
    ];

    // Relasi ke tabel users
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke tabel likes
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Relasi ke tabel comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }



public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}


}
