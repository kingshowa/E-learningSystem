<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Content;
use App\Models\Course;
use App\Models\CourseModule;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

    public function manageModules(Request $request)
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

            // deleted courses for a user
        $deleted_modules = Module::onlyTrashed()->where('creator', $user)->get();

        // if ($request->has('supervise')) {
        //     // Admin gets courses of the teachers he supervises
        //     $modules = Module::select('modules.*')
        //         ->join('users', 'modules.creator', '=', 'users.id')
        //         ->where('users.supervisor', '=', $user)
        //         ->get();
        // } else {
        //     // Get courses assigned to the user and courses created by user: teacher or admin
        //     $modules = Module::where('creator', $user)->get();
        // }
        $modules = Module::select('modules.*')
        ->where(function ($query) use ($user) {
        // Check if the user is a supervisor
            $query->whereExists(function ($subquery) use ($user) {
                $subquery->select(DB::raw(1))
                ->from('users')
                ->whereColumn('modules.creator', '=', 'users.id')
                ->where('users.supervisor', '=', $user);
            })
        ->orWhere('creator', $user); // Also include modules created by the user
    })
        ->get();

        $data = [
            'status' => 200,
            'modules' => $modules,
            'deleted_modules' => $deleted_modules
        ];
        return response()->json($data, 200);
    }



    // Create a module
    public function store(Request $request)
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
            $module->creator = $user;
            $module->save();

            if($request->has('parent_course')){
                $courseModule = new CourseModule();

                $courseModule->course_id = $request->parent_course;
                $courseModule->module_id = $module->id;

                $courseModule->save();
            }
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

    // Delete module
public function destroyPermanent($id)
{
    $module = Module::withTrashed()->find($id);

    if ($module == null) {
        $data = [
            'status' => 421,
            'message' => 'This module does not exist.'
        ];
        return response()->json($data, 421);
    } else {
        $module->forceDelete();
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

        $module = Module::find($moduleId); // test 

        if ($module == null) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        }

        $contents = Content::where('module_id', $moduleId)->get();
        $moduleContents = [];

        foreach ($contents as $content) {
            $contentType = Content::select($content->type . 's.*', 'contents.type', 'title', 'duration')
            ->join($content->type . 's', 'contents.id', '=', $content->type . 's.content_id')
            ->where('content_id', $content->id)
        ->get(); // Get the result of the query

        // Check if any results are returned
        if ($contentType->isNotEmpty()) {
            // Push the first item (object) of the collection into $moduleContents
            $moduleContents[] = $contentType->first();
        }
    }

    $courses = Course::select('courses.*')
    ->where(function ($query) use ($user) {
        // Filter courses where the creator is the user OR assigned_to is the user
        $query->where('creator', $user)
        ->orWhere('assigned_to', $user);
    })
    ->get();

    // if (count($moduleContents) == 0) {
    //     $data = [
    //         'status' => 404,
    //         'message' => 'There are no contents in this module, add new content!'
    //     ];
    //     return response()->json($data, 404);
    //} else {
    $module->course_id = 0;
    $module->contents = $moduleContents;

    $data = [
        'status' => 200,
        'module' => $module,
        'courses' => $courses,
    ];
    return response()->json($data, 200);
    //}
}
}
