<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;


class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'body', 'cover_image', 'pinned', 'user_id','deleted_at','created_at','updated_at'
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('user_post_stats'); 
        });

        static::deleted(function () {
            Cache::forget('user_post_stats'); 
        });
    }
}
