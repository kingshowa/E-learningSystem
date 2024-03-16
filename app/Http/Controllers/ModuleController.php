<?php

namespace App\Http\Controllers;

use App\Models\Quize;
use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Content;
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

    // Get a module by id
    public function getModuleById($id)
    {
        $module = Module::find($id);

        if ($module == null) {
            $data = [
                'status' => 400,
                'message' => 'Module not found'
            ];
            return response()->json($data, 400);
        } else {
            $data = [
                'status' => 200,
                'module' => $module
            ];
            return response()->json($data, 200);
        }
    }

    // Create a module
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
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

    // Update module details
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
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

    // Delete module
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

    // Restore deleted module
    public function restoreModule($id)
    {
        $module = Module::onlyTrashed()->find($id);
        if ($module != null) {
            $module->restore();
            $data = [
                'status' => 200,
                'message' => 'Module successfully restored'
            ];
            return response()->json($data, 200);
        } else {
            $data = [
                'status' => 400,
                'message' => 'Module not found'
            ];
            return response()->json($data, 400);
        }
    }


    // Get module contents
    public function getModuleContents($moduleId)
    {

        $module = Module::find($moduleId); // test 

        if ($module == null) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        }

        $contents = Content::where('moduleId', $moduleId)->get();

        $moduleContents = [];
        foreach ($contents as $content) {
            $contentType = Content::select($content->type . 's.*', 'contents.type')
                ->join($content->type . 's', 'contents.id', '=', $content->type . 's.contentId')
                ->where('contentId', $content->id)->get();

            $moduleContents[] = $contentType;
        }

        if (count($moduleContents) == 0) {
            $data = [
                'status' => 404,
                'message' => 'There are no contents in this module, add new content!'
            ];
            return response()->json($data, 404);
        } else {
            $data = [
                'status' => 200,
                'moduleContents' => $moduleContents
            ];
            return response()->json($data, 200);
        }
    }
}
