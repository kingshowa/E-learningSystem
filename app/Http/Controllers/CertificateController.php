<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Certificate;
use App\Models\Course;

class CertificateController extends Controller
{
    public function index($name, $id){
        $userId=Auth::user()->id;

        $certificate = Certificate::with('user', $name)
            ->where($name.'_id', $id)
            ->where('user_id', $userId)
            ->first();

        $duration = Course::select(DB::raw('SUM(modules.duration) as total_duration'))
                ->where('courses.id', $id)
                ->join('course_modules', 'courses.id', '=', 'course_modules.course_id')
                ->join('modules', 'course_modules.module_id', '=', 'modules.id')
                ->groupBy('courses.id', 'courses.name')
                ->first();

            if($duration)
                $certificate->course->duration=$duration->total_duration;
            else
                $certificate->course->duration=0;
            $modules = Course::select('courses.id', 'courses.name', DB::raw('COUNT(modules.id) as total_modules'))
                ->where('courses.id', $id)
                ->join('course_modules', 'courses.id', '=', 'course_modules.course_id')
                ->join('modules', 'course_modules.module_id', '=', 'modules.id')
                ->groupBy('courses.id', 'courses.name')
                ->first();
            if($modules)
                $certificate->course->modules = $modules->total_modules;
            else
                $certificate->course->modules = 0;
            $certificate->date = substr($certificate->created_at, 0, 10);
        $data=[
            'status'=>200,
            'certificate'=>$certificate,
        ];
        return response()->json($data, 200);
    }

    public function verify($id){

        $certificate = Certificate::with('user', 'program', 'course')
            ->where('token', $id)
            ->first();

        $duration = Course::select(DB::raw('SUM(modules.duration) as total_duration'))
                ->where('courses.id', $certificate->course->id)
                ->join('course_modules', 'courses.id', '=', 'course_modules.course_id')
                ->join('modules', 'course_modules.module_id', '=', 'modules.id')
                ->groupBy('courses.id', 'courses.name')
                ->first();

            if($duration)
                $certificate->course->duration=$duration->total_duration;
            else
                $certificate->course->duration=0;
            $modules = Course::select('courses.id', 'courses.name', DB::raw('COUNT(modules.id) as total_modules'))
                ->where('courses.id', $certificate->course->id)
                ->join('course_modules', 'courses.id', '=', 'course_modules.course_id')
                ->join('modules', 'course_modules.module_id', '=', 'modules.id')
                ->groupBy('courses.id', 'courses.name')
                ->first();
            if($modules)
                $certificate->course->modules = $modules->total_modules;
            else
                $certificate->course->modules = 0;
            $certificate->date = substr($certificate->created_at, 0, 10);
        $data=[
            'status'=>200,
            'certificate'=>$certificate,
        ];
        return response()->json($data, 200);
    }
}
