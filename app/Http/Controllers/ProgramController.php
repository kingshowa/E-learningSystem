<?php

namespace App\Http\Controllers;

use App\Models\ProgramCourse;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\ProgramProgress;

use App\Models\User;
use App\Models\Enrollment;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ProgramController extends Controller
{
    // list all programs ready to be used by students
    public function index()
    {
        $programs = Program::where("enabled", true)->get();

        foreach ($programs as $program) {
            $program->photo = asset('storage/' . substr($program->photo, 7));
        }

        $data = [
            'status' => 200,
            'programs' => $programs
        ];
        return response()->json($data, 200);
    }

    // Get a program with id
    public function getProgramById($id)
    {
        $program = Program::find($id);

        if ($program == null) {
            $data = [
                'status' => 400,
                'message' => 'Program not found'
            ];
            return response()->json($data, 400);
        } else {
            $program->photo = asset('storage/' . substr($program->photo, 7));

            $user = User::find($program->creator);
            $program->teacher = $user;

            $courses = Program::select('programs.id', 'programs.name', DB::raw('COUNT(courses.id) as total_courses'))
                ->where('programs.id', $id)
                ->join('program_courses', 'programs.id', '=', 'program_courses.program_id')
                ->join('courses', 'program_courses.course_id', '=', 'courses.id')
                ->groupBy('programs.id', 'programs.name')
                ->first();
            if($courses)
                $program->courses = $courses->total_courses;
            else
                $program->courses = 0;
            
            $data = [
                'status' => 200,
                'program' => $program
            ];
            return response()->json($data, 200);
        }
    }

    // Get admin programs: can be deleted or not
    public function getAdminPrograms(Request $request)
    {
        $user = Auth::user()->id;

        $deleted_programs = Program::onlyTrashed()->where('creator', $user)->get();

        // all programs created by a user
        $programs = Program::where('creator', $user)->get();

        $data = [
            'status' => 200,
            'programs' => $programs,
            'deleted_programs' => $deleted_programs
        ];
        return response()->json($data, 200);
    }

    //create and save new program
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
            $program = new Program;

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
                    $program->photo = $path;
                }
            }

            $program->name = $request->name;
            $program->description = $request->description;
            $program->price = $request->price;
            $program->creator = $user;
            $program->enabled = false;

            if ($program->save()) {
                $data = [
                    'status' => 200,
                    'message' => 'Program created successfully'
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

    //Update program detais
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
            $program = Program::find($id);

            if ($program == null) {
                $data = [
                    'status' => 421,
                    'message' => 'This program does not exist.'
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
                        Storage::delete($program->photo);
                        $path = $request->file('photo')->store('public/images');
                        $program->photo = $path;
                    }
                }
                $program->name = $request->name;
                $program->description = $request->description;
                $program->price = floatval($request->price);
                $program->enabled = $request->enabled;
                $program->save();

                $data = [
                    'status' => 200,
                    'message' => 'Program updated successfully'
                ];
                return response()->json($data, 200);
            }
        }
    }

    //Update program status
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
            $program = Program::find($id);

            if ($program == null) {
                $data = [
                    'status' => 421,
                    'message' => 'This program does not exist.'
                ];
                return response()->json($data, 421);
            } else {
                $status = 'enabled';
                if ($request->enabled == 0)
                    $status = 'disabled';

                $program->enabled = $request->enabled;
                $program->save();
                $data = [
                    'status' => 200,
                    'message' => 'Program ' . $status . ' successfully'
                ];
                return response()->json($data, 200);
            }
        }
    }

    //Delete program
    public function destroy($id)
    {
        $program = Program::find($id);

        if ($program == null) {
            $data = [
                'status' => 421,
                'message' => 'This program does not exist.'
            ];

            return response()->json($data, 421);
        } else {
            $program->delete();

            $data = [
                'status' => 200,
                'message' => 'Program deleted successfully'
            ];

            return response()->json($data, 200);
        }
    }

    // Delete program permanently
    public function destroyPermanent($id)
    {
        $program = Program::withTrashed()->find($id);

        if ($program == null) {
            $data = [
                'status' => 421,
                'message' => 'This module does not exist.'
            ];
            return response()->json($data, 421);
        } else {
            $program->forceDelete();
            $data = [
                'status' => 200,
                'message' => 'Module deleted successfully'
            ];
            return response()->json($data, 200);
        }
    }

    // Restore deleted program
    public function restoreProgram($id)
    {
        $program = Program::onlyTrashed()->find($id);
        if ($program != null) {
            $program->restore();
            $data = [
                'status' => 200,
                'message' => 'Program successfully restored'
            ];
            return response()->json($data, 200);
        } else {
            $data = [
                'status' => 400,
                'message' => 'Program not found'
            ];
            return response()->json($data, 400);
        }
    }

    // Add a programe course
    public function addProgramCourse(Request $request)
    {
        $course = Course::find($request->courseId);
        $program = Program::find($request->programId);

        if ($course == null) {
            $data = [
                'status' => 404,
                'message' => 'Course not found'
            ];
            return response()->json($data, 404);
        } else if ($program == null) {
            $data = [
                'status' => 404,
                'message' => 'Program not found'
            ];
            return response()->json($data, 404);
        } else {
            $programCourse = new ProgramCourse();

            $programCourse->course_id = $request->courseId;
            $programCourse->program_id = $request->programId;

            $programCourse->save();

            $data = [
                'status' => 200,
                'message' => 'Course successfully added'
            ];

            return response()->json($data, 200);
        }
    }

    // delete a program course
    public function removeProgramCourse($courseId, $programId)
    {
        $programCourse = ProgramCourse::where('program_id', $programId)->where('course_id', $courseId);
        if ($programCourse->delete()) {
            $data = [
                'status' => 200,
                'message' => 'Course successfully removed from this program'
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

    // List all courses in a particular program
    public function listProgramCourses($programId)
    {
        $program = Program::find($programId); // test 

        if ($program == null) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        }

        $courses = Course::select('courses.*')
            ->join('program_courses', 'courses.id', '=', 'program_courses.course_id')
            ->where('program_courses.program_id', $programId)->get();

        $program->courses = $courses;
        $program->photo = asset('storage/' . substr($program->photo, 7));

        $data = [
            'status' => 200,
            'program' => $program
        ];
        return response()->json($data, 200);

    }


    // List all courses in each program
    public function listProgramsWithCourses()
    {
        $programs = Program::with('courses')->where('enabled', 1)->get();

        foreach ($programs as $program) {
                $program->photo = asset('storage/' . substr($program->photo, 7));

            foreach ($program->courses as $course) {
                $course->photo = asset('storage/' . substr($course->photo, 7));
            }
        }

        
        $coursesNotInPrograms = Course::where('enabled', 1)
            ->whereNotIn('id', function ($query) {
            $query->select('course_id')
                ->from('program_courses');
            })
            ->get();

        foreach ($coursesNotInPrograms as $course) {
                $course->photo = asset('storage/' . substr($course->photo, 7));
        }

        $program1 = new Program();
        $program1->id=0;
        $program1->name='Single';
        $program1->courses=$coursesNotInPrograms;

        $programs->push($program1);

        $data = [
            'status' => 200,
            'programs' => $programs
        ];
        return response()->json($data, 200);

    }

    // Enroll into a program
    public function registerProgram($programId)
    {
        $user = Auth::user()->id;

        // register
        $registered = Enrollment::where('user_id', $user)
            ->where('program_id', $programId)->get();

        if ($registered->isEmpty()) {
            $enrollment = new Enrollment();

            $enrollment->program_id = $programId;
            $enrollment->user_id = $user;

            $enrollment->save();

            
            $data = [
                'status' => 200,
                'message' => 'Program registration is successfull.'
            ];
            return response()->json($data, 200);
        } else {
            $data = [
                'status' => 401,
                'message' => 'Already registered in this program.'
            ];
            return response()->json($data, 401);
        }
    }

    //List of enrolled programs by a student
    public function enrolledPrograms()
    {
        $user = Auth::user();

        $programs = Program::with(['courses'])
            ->whereHas('enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        foreach ($programs as $program){
            $program->progress = ProgramProgress::where('program_id', $program->id)
            ->where('user_id', $user->id)
            ->first()->overal_completion;

            $program->photo = asset('storage/' . substr($program->photo, 7));

            foreach ($program->courses as $course){
                $course->progress = CourseProgress::where('course_id', $course->id)
                ->where('user_id', $user->id)
                ->first()->overal_completion;
                $course->photo = asset('storage/' . substr($course->photo, 7));

            }
        }

        $completedPrograms = $programs->filter(function ($program) {
            return $program->progress >= 100; 
        })->values();

        $uncompletedPrograms = $programs->filter(function ($program) {
            return $program->progress < 100; 
        })->values();

        $data = [
            'status' => 200,
            'programs' => $uncompletedPrograms,
            'completed_programs' => $completedPrograms
        ];
        return response()->json($data, 200);

    }
}
