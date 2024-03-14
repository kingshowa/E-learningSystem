<?php

use App\Http\Controllers\ModuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\CourseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Only for production
//$_SESSION['admin']=1;
//$_SESSION['teacher']=2;
$_SESSION['student']=3;

//program
Route::get('programs', [ProgramController::class,'index']);
Route::get('program/{id}', [ProgramController::class,'getProgramById']);
Route::get('admin/programs', [ProgramController::class,'getAdminPrograms']); // can be achived or not, enabled or disabled
Route::post('program', [ProgramController::class,'store']);
Route::put('program/edit/{id}', [ProgramController::class,'update']);
Route::put('program/enable/{id}', [ProgramController::class,'updateStatus']);
Route::get('program/restore/{id}', [ProgramController::class,'restoreProgram']); // retrieves deleted program
Route::delete('program/delete/{id}', [ProgramController::class,'destroy']);
Route::post('program/add/course', [ProgramController::class,'addProgramCourse']);
Route::delete('program/delete/{courseId}/{programId}', [ProgramController::class,'removeProgramCourse']);
Route::get('program/courses/{id}', [ProgramController::class,'listProgramCourses']);

//course
Route::get('courses', [CourseController::class,'index']);
Route::get('course/{id}', [CourseController::class,'getCourseById']);
Route::get('courses/manage', [CourseController::class,'manageCourses']);
Route::get('courses/enrolled', [CourseController::class,'enrolledCourses']);
Route::get('course/restore/{id}', [CourseController::class,'restoreCourse']); // retrieves deleted course
Route::post('course', [CourseController::class,'store']);
Route::put('course/edit/{id}', [CourseController::class,'update']);
Route::put('course/enable/{id}', [CourseController::class,'updateStatus']);
Route::delete('course/delete/{id}', [CourseController::class,'destroy']);
Route::post('course/add/module', [CourseController::class,'addCourseModule']);
Route::delete('course/delete/{courseId}/{moduleId}', [CourseController::class,'removeCourseModule']);
Route::get('course/modules/{id}', [CourseController::class,'listCourseModules']);

//module
Route::get('modules', [ModuleController::class,'index']);
Route::post('module', [ModuleController::class,'store']);
Route::put('module/edit/{id}', [ModuleController::class,'update']);
Route::delete('module/delete/{id}', [ModuleController::class,'destroy']);