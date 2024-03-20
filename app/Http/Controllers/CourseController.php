<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Module;
use App\Models\CourseModule;
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
        // verify connection
        if (isset($_SESSION['admin'])) {
            $user = $_SESSION['admin'];
        } else if (isset($_SESSION['teacher'])) {
            $user = $_SESSION['teacher'];
        } else {
            $data = [
                'status' => 400,
                'message' => 'User not connected'
            ];
            return response()->json($data, 400);
        }

        if ($request->has('deleted')) {
            // deleted courses for a user
            $courses = Course::onlyTrashed()->where('creator', $user)->get();
        } else if ($request->has('supervise')) {
            // Admin gets courses of the teachers he supervises
            $courses = Course::select('courses.*')
                ->join('users', 'courses.creator', '=', 'users.id')
                ->where('users.supervisor', '=', $user)
                ->get();
        } else {
            // Get courses assigned to the user and courses created by user: teacher or admin
            $courses = Course::where('creator', $user)->orWhere('assigned_to', $user)->get();
        }
        $data = [
            'status' => 200,
            'courses' => $courses
        ];
        return response()->json($data, 200);
    }

    //List of enrolled courses by a student
    public function enrolledCourses()
    {
        // Verify connection
        if (!isset($_SESSION['student'])) {
            $data = [
                'status' => 400,
                'message' => 'User not connected'
            ];
            return response()->json($data, 400);
        } else {
            $courses = Course::select('courses.*')
                ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
                ->where('enrollments.user_id', $_SESSION['student'])->get();
            $data = [
                'status' => 200,
                'courses' => $courses
            ];
            return response()->json($data, 200);
        }
    }

    // Create a new course
    public function store(Request $request)
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
            $course = new Course;

            $course->name = $request->name;
            $course->code = $request->code;
            $course->description = $request->description;
            $course->price = $request->price;
            $course->level = $request->level;
            $course->photo = $request->photo;
            $course->creator = $request->creator;
            $course->assigned_to = $request->assigned_to;
            $course->completed = $request->completed;
            $course->enabled = $request->enabled;

            if ($course->save()) {
                $disc = new Discussion();
                $disc->course_id = $course->id;
                $disc->save();
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
                $course->name = $request->name;
                $course->code = $request->code;
                $course->description = $request->description;
                $course->price = $request->price;
                $course->level = $request->level;
                $course->photo = $request->photo;
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
        $courseModules = Module::select('modules.*')
            ->join('course_modules', 'modules.id', '=', 'course_modules.module_id')
            ->where('course_modules.course_id', $courseId)->get();

        if ($courseModules->isEmpty()) {
            $data = [
                'status' => 404,
                'message' => 'There are no modules in this course, add new module!'
            ];
            return response()->json($data, 404);
        } else {
            $data = [
                'status' => 200,
                'courseModules' => $courseModules
            ];
            return response()->json($data, 200);
        }
    }

    // Enroll into a course
    public function registerCourse($courseId)
    {
        // Verify connection
        if (!isset($_SESSION['student'])) {
            $data = [
                'status' => 400,
                'message' => 'User not connected'
            ];
            return response()->json($data, 400);
        } else { // register
            $registered = Enrollment::where('user_id', $_SESSION['student'])
                ->where('course_id', $courseId)->get();

            if ($registered->isEmpty()) {
                $enrollment = new Enrollment();

                $enrollment->course_id = $courseId;
                $enrollment->user_id = $_SESSION['student'];

                $enrollment->save();

                $data = [
                    'status' => 200,
                    'message' => 'Registration is successfull.'
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

}
