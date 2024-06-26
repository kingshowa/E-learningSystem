<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required',
                    'role' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = new User;

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
            $user->email = $request->email;
            $user->role = $request->role;
            $user->password = $request->password;
            $user->phone = $request->phone;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function createAdminUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required',
                    'role' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = new User;

            $user->supervisor = $request->user()->id;
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->password = $request->password;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Admin User Created Successfully'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update User
     * @param Request $request
     * @return User 
     */
    public function updateUser(Request $request, $id)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'password' => 'required',
                    'role' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::find($id);

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
            $user->email = $request->email;
            $user->role = $request->role;
            $user->supervisor = $request->supervisor;
            $user->password = $request->password;
            $user->phone = $request->phone;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User Updated Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            $user->photo = asset('storage/' . substr($user->photo, 7));

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken,
                'role' => $user->role,
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Logout The User
     * @param Request $request
     */
    public function logoutUser(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $user->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'User logged out successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password
     * @param Request $request
     */
    public function resetPassword(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            if(Hash::check($request->old_password, $user->password)){
                $user->password=$request->password;
                $user->save();
                $user->tokens()->delete();
            }
            

            return response()->json([
                'status' => true,
                'message' => 'password changed successfully',
                'pass' => $user->password
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

}
