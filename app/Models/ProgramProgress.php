<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_id',
        'overal_completion',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
