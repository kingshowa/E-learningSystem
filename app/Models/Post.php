<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'discussin_id',
        'create_by',
        'topic',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }
}
