<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'create_by',
        'topic',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
