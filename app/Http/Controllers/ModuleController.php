<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use Validator;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::all();

        $data = [
            'status' => 200,
            'modules' => $modules
        ];

        return response()->json($data, 200);
    }

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
            $module = new Module;

            $module->name = $request->name;
            $module->description = $request->description;
            $module->code = $request->code;
            $module->duration = $request->duration;
            $module->creator = $request->creator;

            $module->save();

            $data = [
                'status' => 200,
                'message' => 'Module created successfully'
            ];

            return response()->json($data, 200);
        }
    }

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
            $module = Module::find($id);

            if ($module == null) {
                $data = [
                    'status' => 421,
                    'message' => 'This module does not exist.'
                ];

                return response()->json($data, 421);
            } else {
                $module->name = $request->name;
                $module->description = $request->description;
                $module->code = $request->code;
                $module->duration = $request->duration;
                $module->creator = $request->creator;

                $module->save();

                $data = [
                    'status' => 200,
                    'message' => 'Module updated successfully'
                ];

                return response()->json($data, 200);
            }
        }
    }

    public function destroy($id)
    {
        $module = Module::find($id);

        if ($module == null) {
            $data = [
                'status' => 421,
                'message' => 'This module does not exist.'
            ];

            return response()->json($data, 421);
        } else {
            $module->delete();

            $data = [
                'status' => 200,
                'message' => 'Module deleted successfully'
            ];

            return response()->json($data, 200);
        }
    }
}
