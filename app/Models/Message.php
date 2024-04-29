<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'sent_by',
        'sent_to',
        'text',
        'attachment',
    ];

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'sent_to');
    }
}
