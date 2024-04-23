<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'level',
        'price',
        'photo',
        'creator',
        'assigned_to',
        'completed',
        'enabled',
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'course_modules');
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'program_courses');
    }
}
