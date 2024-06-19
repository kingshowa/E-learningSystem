<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\ProgramProgress;
use App\Models\CourseProgress;
use App\Models\Course;
use App\Models\Module;

use App\Models\User;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function adminAnalytics()
    {
        // Assuming you have an authenticated admin user
        $adminId = Auth::user()->id;

        // Retrieve all programs created by the admin
        $programs = Program::where('creator', $adminId)
            ->where('enabled', 1)->get();

        $sixMonthsAgo = Carbon::now()->subMonths(6);

        foreach ($programs as $program) {
            // Number of enrollments grouped per month within the past 6 months including the current month
            $enrollmentsGroupedByMonth = Enrollment::where('program_id', $program->id)
                ->where('created_at', '>=', $sixMonthsAgo->startOfMonth())
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'month' => Carbon::create()->month($item->month)->format('M'), // Convert month number to short name
                        'count' => $item->count,
                    ];
                });
                
                $labels = [];
                $counts = [];

                foreach ($enrollmentsGroupedByMonth as $enrollment) {
                    $labels[] = $enrollment["month"];
                    $counts[] = $enrollment["count"];
                }

                $datasets = (object) [
                    'label'=>'Enrollments',
                    'data'=>$counts
                ];
                $program->enrollments = (object) [
                    'labels' => $labels,
                    'datasets' => $datasets,
                ];
        }

        $counts = DB::table('users')
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get();

        $groupedCounts = $counts->pluck('total', 'role');

        $data=[
            'status'=>200,
            'programs'=>$programs,
            'counts'=>$groupedCounts,
        ];
        return response()->json($data, 200);
    }


    public function teacherAnalytics()
    {
        // Assuming you have an authenticated admin user
        $user = Auth::user()->id;

        $courses = Course::select('courses.*')
            ->where('enabled', 1)
            ->where(function ($query) use ($user) {
                // Filter courses where the creator is the user OR assigned_to is the user
                $query->where('creator', $user)
                    ->orWhere('assigned_to', $user);
            })
            ->get();

        $sixMonthsAgo = Carbon::now()->subMonths(6);

        foreach ($courses as $course) {
            // Number of enrollments grouped per month within the past 6 months including the current month
            $enrollmentsGroupedByMonth = CourseProgress::where('course_id', $course->id)
                ->where('created_at', '>=', $sixMonthsAgo->startOfMonth())
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'year' => $item->year,
                        'month' => Carbon::create()->month($item->month)->format('M'), // Convert month number to short name
                        'count' => $item->count,
                    ];
                });
                
                
                $labels = [];
                $counts = [];

                foreach ($enrollmentsGroupedByMonth as $enrollment) {
                    $labels[] = $enrollment["month"];
                    $counts[] = $enrollment["count"];
                }

                $datasets = (object) [
                    'label'=>'Enrollments',
                    'data'=>$counts
                ];
                $course->enrollments = (object) [
                    'labels' => $labels,
                    'datasets' => $datasets,
                ];
        }

        $courseIds=[];
        foreach ($courses as $course){
            $courseIds[]= $course["id"];
        }

        $modules = DB::table('course_modules')
        ->whereIn('course_id', $courseIds)
        ->distinct('module_id')
        ->count('module_id');


        $totalStudents = DB::table('course_progress')
            ->whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        $data=[
            'status'=>200,
            'courses'=>$courses,
            'modulesCount'=>$modules,
            'studentsCount'=>$totalStudents,
        ];

        return response()->json($data, 200);
    }

    public function studentsEnrolledInProgram(Request $request, $programId)
    {
        // Assuming you have an authenticated admin user
        $adminId = Auth::user()->id;

        // Retrieve the specified program created by the admin
        $program = Program::where('creator', $adminId)->findOrFail($programId);

        $enrollments = Enrollment::where('program_id', $programId)
            ->with('user')
            ->get();

        foreach ($enrollments as $enrollment){
            $progress = ProgramProgress::where('user_id', $enrollment->user_id)
                ->where('program_id', $programId)->first();
            $enrollment->progress=$progress->overal_completion;
            $enrollment->last_update=substr($progress->updated_at, 0, 10);
            $enrollment->enrollment_date=substr($enrollment->created_at, 0, 10);

            $enrollment->user->photo= asset('storage/'. substr($enrollment->user->photo, 7));

        }

        return response()->json([
            'program' => $program,
            'students' => $enrollments,
        ]);
    }

    public function studentsEnrolledInCourse(Request $request, $courseId)
    {
        $user = Auth::user()->id;

        $course = Course::findOrFail($courseId);

        $enrollments = CourseProgress::where('course_id', $course->id)
                ->with('user')
                ->get();

        foreach ($enrollments as $enrollment){
            $enrollment->progress=$enrollment->overal_completion;
            $enrollment->last_update=substr($enrollment->updated_at, 0, 10);
            $enrollment->enrollment_date=substr($enrollment->created_at, 0, 10);
            $enrollment->user->photo= asset('storage/'. substr($enrollment->user->photo, 7));
        }

        return response()->json([
            'course' => $course,
            'students' => $enrollments,
        ]);
    }
}
