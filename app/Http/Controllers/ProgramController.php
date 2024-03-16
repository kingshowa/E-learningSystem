<?php

namespace App\Http\Controllers;

use App\Models\ProgramCourse;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Course;
use App\Models\Enrollment;
use Validator;

class ProgramController extends Controller
{
    // list all programs ready to be used by students
    public function index()
    {
        $programs = Program::where("enabled", true)->get();

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
            // deleted programs for a user
            $programs = Program::onlyTrashed()->where('creator', $user)->get();
        } else {
            // all programs created by a user
            $programs = Program::where('creator', $user)->get();
        }
        $data = [
            'status' => 200,
            'programs' => $programs
        ];
        return response()->json($data, 200);
    }

    //create and save new program
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
            $program = new Program;

            $program->name = $request->name;
            $program->description = $request->description;
            $program->price = $request->price;
            $program->photo = $request->photo;
            $program->creator = $request->creator;
            $program->enabled = $request->enabled;

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
                $program->name = $request->name;
                $program->description = $request->description;
                $program->price = $request->price;
                $program->photo = $request->photo;
                $program->creator = $request->creator;
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

            $programCourse->courseId = $request->courseId;
            $programCourse->programId = $request->programId;

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
        $programCourse = ProgramCourse::where('programId', $programId)->where('courseId', $courseId);
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
        $programCourses = Course::select('courses.*')
            ->join('program_courses', 'courses.id', '=', 'program_courses.courseId')
            ->where('program_courses.programId', $programId)->get();

        if ($programCourses->isEmpty()) {
            $data = [
                'status' => 404,
                'message' => 'There are no courses in this program, add new course!'
            ];
            return response()->json($data, 404);
        } else {
            $data = [
                'status' => 200,
                'programCourses' => $programCourses
            ];
            return response()->json($data, 200);
        }
    }

    // Enroll into a program
    public function registerProgram($programId)
    {
        // Verify connection
        if (!isset($_SESSION['student'])) {
            $data = [
                'status' => 400,
                'message' => 'User not connected'
            ];
            return response()->json($data, 400);
        } else { // register
            $registered = Enrollment::where('studentId', $_SESSION['student'])
                ->where('programId', $programId)->get();

            if ($registered->isEmpty()) {
                $enrollment = new Enrollment();

                $enrollment->programId = $programId;
                $enrollment->studentId = $_SESSION['student'];

                $enrollment->save();

                $data = [
                    'status' => 200,
                    'message' => 'Registration is successfull.'
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
    }

    //List of enrolled programs by a student
    public function enrolledPrograms()
    {
        // Verify connection
        if (!isset($_SESSION['student'])) {
            $data = [
                'status' => 400,
                'message' => 'User not connected'
            ];
            return response()->json($data, 400);
        } else {
            $programs = Program::select('programs.*')
                ->join('enrollments', 'programs.id', '=', 'enrollments.programId')
                ->where('studentId', $_SESSION['student'])
                ->get();
            $data = [
                'status' => 200,
                'programs' => $programs
            ];
            return response()->json($data, 200);
        }
    }
}
