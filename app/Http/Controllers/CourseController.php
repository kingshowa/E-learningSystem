<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Validator;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();

        $data = [
            'status' => 200,
            'courses' => $courses
        ];

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'enabled' => 'required',
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
            $course->enabled = $request->enabled;

            $course->save();

            $data = [
                'status' => 200,
                'message' => 'Course created successfully'
            ];

            return response()->json($data, 200);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'enabled' => 'required',
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

    
}
