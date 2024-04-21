<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
    // Create a user
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'message' => $validator->messages()
            ];
            return response()->json($data, 422);
        } else {
            $user = new User;

            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->supervisor = $request->supervisor;
            $user->password = $request->password;
            $user->date_of_birth = $request->date_of_birth;
            $user->save();

            $data = [
                'status' => 200,
                'message' => 'User created successfully'
            ];
            return response()->json($data, 200);
        }
    }
}
