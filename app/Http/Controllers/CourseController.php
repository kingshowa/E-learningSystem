<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use App\Models\CourseModule;
use App\Models\CourseProgress;
use App\Models\ProgramCourse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


use Validator;

class CourseController extends Controller
{
    // List all courses ready to be used by students
    public function index()
    {
        $courses = Course::where("enabled", true)->get();

        if ($courses->count() == 0) {
            $data = [
                'status' => 400,
                'message' => 'No available courses'
            ];
            return response()->json($data, 400);
        } else {
            foreach ($courses as $course) {
                $course->photo = asset('storage/' . substr($course->photo, 7));
            }

            $data = [
                'status' => 200,
                'courses' => $courses
            ];
            return response()->json($data, 200);
        }
    }

    // Get a course by id
    public function getCourseById($id)
    {
        $course = Course::find($id);

        if ($course == null) {
            $data = [
                'status' => 400,
                'message' => 'Course not found'
            ];
            return response()->json($data, 400);
        } else {
            $course->photo = asset('storage/' . substr($course->photo, 7));

            $assignedToUserId = $course->assigned_to ? $course->assigned_to : $course->creator;
            $user = User::find($assignedToUserId);
            $course->teacher = $user;
            $duration = Course::select(DB::raw('SUM(modules.duration) as total_duration'))
                ->where('courses.id', $id)
                ->join('course_modules', 'courses.id', '=', 'course_modules.course_id')
                ->join('modules', 'course_modules.module_id', '=', 'modules.id')
                ->groupBy('courses.id', 'courses.name')
                ->first();

            if($duration)
                $course->duration=$duration->total_duration;
            else
                $course->duration=0;
            $modules = Course::select('courses.id', 'courses.name', DB::raw('COUNT(modules.id) as total_modules'))
                ->where('courses.id', $id)
                ->join('course_modules', 'courses.id', '=', 'course_modules.course_id')
                ->join('modules', 'course_modules.module_id', '=', 'modules.id')
                ->groupBy('courses.id', 'courses.name')
                ->first();
            if($modules)
                $course->modules = $modules->total_modules;
            else
                $course->modules = 0;

            $data = [
                'status' => 200,
                'course' => $course
            ];
            return response()->json($data, 200);
        }
    }

    // Get admin/teacher courses: can be deleted or not, supervised courses, created, assigned to
    public function manageCourses(Request $request)
    {
        $user = Auth::user()->id;

        // deleted courses for a user
        $deleted_courses = Course::onlyTrashed()->where('creator', $user)->get();

        $courses = Course::select('courses.*')
            ->where(function ($query) use ($user) {
                // Filter courses where the creator is the user OR assigned_to is the user
                $query->where('creator', $user)
                    ->orWhere('assigned_to', $user);
            })
            ->get();

        $data = [
            'status' => 200,
            'courses' => $courses,
            'deleted_courses' => $deleted_courses,
        ];
        return response()->json($data, 200);
    }

    // Get admin/teacher courses: can be deleted or not, supervised courses, created, assigned to
    public function manageCoursesEx($programId)
    {
        $user = Auth::user()->id;

        $courses = Course::select('courses.*')
            ->where(function ($query) use ($user, $programId) {
                // Check if the user is a supervisor or creator of the course
                $query->where(function ($subquery) use ($user) {
                    $subquery->whereExists(function ($existsQuery) use ($user) {
                        $existsQuery->select(DB::raw(1))
                            ->from('users')
                            ->whereColumn('courses.creator', 'users.id')
                            ->where('users.supervisor', $user);
                    })
                        ->orWhere('creator', $user);
                })
                    // Filter out courses that are associated with the specified program
                    ->whereDoesntHave('programs', function ($doesntHaveQuery) use ($programId) {
                    $doesntHaveQuery->where('program_id', $programId);
                });
            })
            ->get();

        $data = [
            'status' => 200,
            'courses' => $courses,
        ];
        return response()->json($data, 200);
    }

    //List of enrolled courses by a student
    public function enrolledCourses()
    {
        $user = Auth::user();

        $courses = Course::select('courses.*')
            ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.user_id', $user->id)->get();

        foreach ($courses as $course){
            $course->progress = CourseProgress::where('course_id', $course->id)->first()->overal_completion;
            $course->photo = asset('storage/' . substr($course->photo, 7));
        }

        $completedCourses = $courses->filter(function ($course) {
            return $course->progress >= 100; 
        })->values();

        $uncompletedCourses = $courses->filter(function ($course) {
            return $course->progress < 100; 
        })->values();

        $data = [
            'status' => 200,
            'courses' => $uncompletedCourses,
            'completed_courses' => $completedCourses,
            'user' => $user
        ];
        return response()->json($data, 200);

    }

    // Create a new course
    public function store(Request $request)
    {
        $user = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'message' => $validator->messages()
            ];

            return response()->json($data, 422);
        } else {
            $course = new Course;

            if ($request->hasFile('photo')) {
                $validator = Validator::make($request->all(), [
                    'photo' => 'required|mimes:jpeg,jpg,png,tiff|max:5000', //Adjust max file size as needed
                ]);

                if ($validator->fails()) {
                    $data = [
                        'status' => 422,
                        'message' => $validator->messages()
                    ];

                    return response()->json($data, 422);
                } else {
                    $path = $request->file('photo')->store('public/images');
                    $course->photo = $path;
                }
            }
            $course->name = $request->name;
            $course->code = $request->code;
            $course->description = $request->description;
            $course->price = $request->price;
            $course->level = $request->level;
            $course->creator = $user;
            $course->assigned_to = $request->assigned_to;
            $course->completed = 0;
            $course->enabled = 0;

            if ($course->save()) {
                $disc = new Discussion();
                $disc->course_id = $course->id;
                $disc->save();

                if ($request->has('program_id')) {
                    $programCourse = new ProgramCourse();

                    $programCourse->course_id = $course->id;
                    $programCourse->program_id = $request->program_id;

                    $programCourse->save();
                }

                $data = [
                    'status' => 200,
                    'message' => 'Course created successfully'
                ];
                return response()->json($data, 200);
            } else {
                $data = [
                    'status' => 423,
                    'message' => 'Store failure'
                ];
                return response()->json($data, 423);
            }
        }
    }

    // Update course detais
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'message' => $validator->messages()
            ];

            return response()->json($data, 422);
        } else {
            $course = Course::find($id);

            if ($course == null) {
                $data = [
                    'status' => 421,
                    'message' => 'This course does not exist.'
                ];

                return response()->json($data, 421);
            } else {
                if ($request->hasFile('photo')) {
                    $validator = Validator::make($request->all(), [
                        'photo' => 'required|mimes:jpeg,jpg,png,tiff|max:5000', //Adjust max file size as needed
                    ]);

                    if ($validator->fails()) {
                        $data = [
                            'status' => 422,
                            'message' => $validator->messages()
                        ];

                        return response()->json($data, 422);
                    } else {
                        Storage::delete($course->photo);
                        $path = $request->file('photo')->store('public/images');
                        $course->photo = $path;
                    }
                }
                $course->name = $request->name;
                $course->code = $request->code;
                $course->description = $request->description;
                $course->price = $request->price;
                $course->level = $request->level;
                $course->creator = $request->creator;
                $course->assigned_to = $request->assigned_to;
                $course->completed = $request->completed;
                $course->enabled = $request->enabled;

                $course->save();

                $data = [
                    'status' => 200,
                    'message' => 'Course updated successfully'
                ];

                return response()->json($data, 200);
            }
        }
    }

    //Update course status: enable/disable
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'message' => $validator->messages()
            ];

            return response()->json($data, 422);
        } else {
            $course = Course::find($id);

            if ($course == null) {
                $data = [
                    'status' => 421,
                    'message' => 'This course does not exist.'
                ];
                return response()->json($data, 421);
            } else {
                $status = 'enabled';
                if ($request->enabled == 0)
                    $status = 'disabled';

                $course->enabled = $request->enabled;
                $course->save();
                $data = [
                    'status' => 200,
                    'message' => 'Course ' . $status . ' successfully'
                ];
                return response()->json($data, 200);
            }
        }
    }

    //Update course status: completed
    public function markCompleted(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'completed' => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'message' => $validator->messages()
            ];

            return response()->json($data, 422);
        } else {
            $course = Course::find($id);

            if ($course == null) {
                $data = [
                    'status' => 421,
                    'message' => 'This course does not exist.'
                ];
                return response()->json($data, 421);
            } else {
                $status = 'completed';
                if ($request->completed == 0)
                    $status = 'uncompleted';

                $course->completed = $request->completed;
                $course->save();
                $data = [
                    'status' => 200,
                    'message' => 'Course ' . $status . ' successfully'
                ];
                return response()->json($data, 200);
            }
        }
    }

    // Delete a course/ archive
    public function destroy($id)
    {
        $course = Course::find($id);

        if ($course == null) {
            $data = [
                'status' => 421,
                'message' => 'This course does not exist.'
            ];

            return response()->json($data, 421);
        } else {
            $course->delete();

            $data = [
                'status' => 200,
                'message' => 'Course deleted successfully'
            ];

            return response()->json($data, 200);
        }
    }

    // Delete module
    public function destroyPermanent($id)
    {
        $course = Course::withTrashed()->find($id);

        if ($course == null) {
            $data = [
                'status' => 421,
                'message' => 'This module does not exist.'
            ];
            return response()->json($data, 421);
        } else {
            $course->forceDelete();
            $data = [
                'status' => 200,
                'message' => 'Module deleted successfully'
            ];
            return response()->json($data, 200);
        }
    }

    // Restore deleted course
    public function restoreCourse($id)
    {
        $course = Course::onlyTrashed()->find($id);
        if ($course != null) {
            $course->restore();
            $data = [
                'status' => 200,
                'message' => 'Course successfully restored'
            ];
            return response()->json($data, 200);
        } else {
            $data = [
                'status' => 400,
                'message' => 'Course not found'
            ];
            return response()->json($data, 400);
        }
    }

    // Add a course module
    public function addCourseModule(Request $request)
    {
        $course = Course::find($request->courseId);
        $module = Module::find($request->moduleId);

        if ($course == null) {
            $data = [
                'status' => 404,
                'message' => 'Course not found'
            ];
            return response()->json($data, 404);
        } else if ($module == null) {
            $data = [
                'status' => 404,
                'message' => 'Module not found'
            ];
            return response()->json($data, 404);
        } else {
            $courseModule = new CourseModule();

            $courseModule->course_id = $request->courseId;
            $courseModule->module_id = $request->moduleId;

            $courseModule->save();

            $data = [
                'status' => 200,
                'message' => 'Module successfully added'
            ];
            return response()->json($data, 200);
        }
    }

    // Delete a course module
    public function removeCourseModule($courseId, $moduleId)
    {
        $courseModule = CourseModule::where('module_id', $moduleId)->where('course_id', $courseId);
        if ($courseModule->delete()) {
            $data = [
                'status' => 200,
                'message' => 'Module successfully removed from this course'
            ];
            return response()->json($data, 200);
        } else {
            $data = [
                'status' => 404,
                'message' => 'Parameters error'
            ];
            return response()->json($data, 404);
        }
    }

    // List all modules in a particular course
    public function listCourseModules($courseId)
    {
        $course = Course::find($courseId); // test 

        if ($course == null) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        }

        $courseModules = Module::select('modules.*')
            ->join('course_modules', 'modules.id', '=', 'course_modules.module_id')
            ->where('course_modules.course_id', $courseId)->get();

        $teachers = User::where('role', '=', 'teacher')->get();

        $course->modules = $courseModules;
        $course->photo = asset('storage/' . substr($course->photo, 7));



        $assignedToUserId = $course->assigned_to ? $course->assigned_to : $course->creator;
            $user = User::find($assignedToUserId);
            $course->teacher = $user;
            $duration = Course::select(DB::raw('SUM(modules.duration) as total_duration'))
                ->where('courses.id', $courseId)
                ->join('course_modules', 'courses.id', '=', 'course_modules.course_id')
                ->join('modules', 'course_modules.module_id', '=', 'modules.id')
                ->groupBy('courses.id', 'courses.name')
                ->first();

            if($duration)
                $course->duration=$duration->total_duration;
            else
                $course->duration=0;
            $t_modules = Course::select('courses.id', 'courses.name', DB::raw('COUNT(modules.id) as total_modules'))
                ->where('courses.id', $courseId)
                ->join('course_modules', 'courses.id', '=', 'course_modules.course_id')
                ->join('modules', 'course_modules.module_id', '=', 'modules.id')
                ->groupBy('courses.id', 'courses.name')
                ->first();
            if($t_modules)
                $course->total_modules = $t_modules->total_modules;
            else
                $course->total_modules = 0;



        $data = [
            'status' => 200,
            'course' => $course,
            'teachers' => $teachers,
        ];
        return response()->json($data, 200);
    }

    // Enroll into a course
    public function registerCourse($courseId)
    {
        $user = Auth::user()->id;

        // register
        $registered = Enrollment::where('user_id', $user)
            ->where('course_id', $courseId)->get();

        if ($registered->isEmpty()) {
            $enrollment = new Enrollment();

            $enrollment->course_id = $courseId;
            $enrollment->user_id = $user;

            $enrollment->save();

            $data = [
                'status' => 200,
                'message' => 'Course registration is successfull.'
            ];
            return response()->json($data, 200);
        } else {
            $data = [
                'status' => 401,
                'message' => 'Already registered in this course.'
            ];
            return response()->json($data, 401);
        }
    }

}
