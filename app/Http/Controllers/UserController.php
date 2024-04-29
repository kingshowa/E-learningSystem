<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
}
