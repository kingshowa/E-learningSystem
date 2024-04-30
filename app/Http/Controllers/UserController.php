<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

use Validator;

class UserController extends Controller
{
    public function getTeachers()
    {
        $teachers = User::where('role', 'teacher')->get();
        $data = [
            'status' => 200,
            'teachers' => $teachers
        ];
        return response()->json($data, 200);
    }

    public function getUsers()
    {
        $users = User::where('enabled', 1)->get();
        $data = [
            'status' => 200,
            'users' => $users
        ];
        return response()->json($data, 200);
    }

   public function getUser()
    {
        $user = Auth::user();

        $user->photo= asset('storage/'. substr($user->photo, 7));
        $data = [
            'status' => 200,
            'user' => $user
        ];
        return response()->json($data, 200);
    }

    public function getAdminUsers()
    {
        $id = Auth::user()->id;

        $users = User::where('supervisor', $id)->get();

        $deleted_users = User::onlyTrashed()->where('supervisor', $id)->get();

        foreach ($users as $user){
            $user->photo= asset('storage/'. substr($user->photo, 7));
        }

        foreach ($deleted_users as $user){
            $user->photo= asset('storage/'. substr($user->photo, 7));
        }

        $data = [
            'status' => 200,
            'users' => $users,
            'deleted_users' => $deleted_users
        ];
        return response()->json($data, 200);
    }

    public function updateUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'surname' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = Auth::user();

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
                    $path = $request->file('photo')->store('public/profiles');
                    $user->photo = $path;
                }
            }

            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->date_of_birth = $request->date_of_birth;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User Updated Successfully'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //Update User status
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
            $User = User::find($id);

            if ($User == null) {
                $data = [
                    'status' => 421,
                    'message' => 'This User does not exist.'
                ];
                return response()->json($data, 421);
            } else {
                $status = 'enabled';
                if ($request->enabled == 0)
                    $status = 'disabled';

                $User->enabled = $request->enabled;
                $User->save();
                $data = [
                    'status' => 200,
                    'message' => 'User ' . $status . ' successfully'
                ];
                return response()->json($data, 200);
            }
        }
    }

    //Delete program
    public function destroy($id)
    {
        $User = User::find($id);

        if ($User == null) {
            $data = [
                'status' => 421,
                'message' => 'This User does not exist.'
            ];

            return response()->json($data, 421);
        } else {
            $User->delete();

            $data = [
                'status' => 200,
                'message' => 'User deleted successfully'
            ];

            return response()->json($data, 200);
        }
    }

    // Delete user permanently
    public function destroyPermanent($id)
    {
        $user = User::withTrashed()->find($id);

        if ($user == null) {
            $data = [
                'status' => 421,
                'message' => 'This module does not exist.'
            ];
            return response()->json($data, 421);
        } else {
            $user->forceDelete();
            $data = [
                'status' => 200,
                'message' => 'Module deleted successfully'
            ];
            return response()->json($data, 200);
        }
    }

    // Restore deleted user
    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->find($id);
        if ($user != null) {
            $user->restore();
            $data = [
                'status' => 200,
                'message' => 'User successfully restored'
            ];
            return response()->json($data, 200);
        } else {
            $data = [
                'status' => 400,
                'message' => 'User not found'
            ];
            return response()->json($data, 400);
        }
    }

    // Add a user course
    public function addUserCourse(Request $request)
    {
        $course = Course::find($request->courseId);

        if ($course == null) {
            $data = [
                'status' => 404,
                'message' => 'Course not found'
            ];
            return response()->json($data, 404);
        } else {
            $course->assigned_to = $request->userId;

            $course->save();

            $data = [
                'status' => 200,
                'message' => 'Course successfully added'
            ];

            return response()->json($data, 200);
        }
    }

    // delete a program course
    public function removeUserCourse($courseId, $userId)
{
    // Find the user's course by courseId and userId
    $userCourse = Course::where('id', $courseId)
                         ->where('assigned_to', $userId)
                         ->first();

    if ($userCourse) {
        // Remove the assignment by setting assigned_to to 0
        $userCourse->assigned_to = 0;

        // Save the changes
        if ($userCourse->save()) {
            $data = [
                'status' => 200,
                'message' => 'Course successfully removed from this user'
            ];
            return response()->json($data, 200);
        } else {
            $data = [
                'status' => 500,
                'message' => 'Failed to save changes'
            ];
            return response()->json($data, 500);
        }
    } else {
        // If the user's course is not found, return a 404 error
        $data = [
            'status' => 404,
            'message' => 'Course not found for this user'
        ];
        return response()->json($data, 404);
    }
}


    // List all courses for particular user
    public function listUserCourses($userId)
    {
        $user = User::find($userId); // test 

        if ($user == null) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        }

        $courses = Course::select('courses.*')
            ->where(function ($query) use ($userId) {
                // Filter courses where the creator is the user OR assigned_to is the user
                $query->where('creator', $userId)
                    ->orWhere('assigned_to', $userId);
            })
            ->get();

        $user->courses = $courses;
        $user->photo = asset('storage/' . substr($user->photo, 7));

        $data = [
            'status' => 200,
            'user' => $user
        ];
        return response()->json($data, 200);
    }

    // List all courses for particular user
    public function listCourses($userId)
    {
        $courses = Course::where('assigned_to', '!=', $userId)->get();

        $data = [
            'status' => 200,
            'courses' => $courses
        ];
        return response()->json($data, 200);
    }
}
