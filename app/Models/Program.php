<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'photo',
        'creator',
        'enabled',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'program_courses');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
