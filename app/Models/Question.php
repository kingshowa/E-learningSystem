<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = [
        'quize_id',
        'context',
        'imageUrl',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quize::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
