<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quize extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'instruction',
        'pass_percentage',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
