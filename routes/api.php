<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ModuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\QuizeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MessageController;

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

// Auth
Route::post('auth/register', [AuthController::class, 'createUser']);
Route::post('auth/admin/register', [AuthController::class, 'createAdminUser'])->middleware('auth:sanctum');
Route::post('auth/update/{id}', [AuthController::class, 'updateUser']);
Route::post('auth/login', [AuthController::class, 'loginUser']);
Route::post('auth/reset', [AuthController::class, 'resetPassword'])->middleware('auth:sanctum');
Route::post('auth/logout', [AuthController::class, 'logoutUser'])->middleware('auth:sanctum');

// user
// Route::post('user', [UserController::class, 'store']);
Route::get('teachers', [UserController::class, 'getTeachers'])->middleware('auth:sanctum');
Route::get('users', [UserController::class, 'getUsers'])->middleware('auth:sanctum');
Route::get('user', [UserController::class, 'getUser'])->middleware('auth:sanctum');
Route::post('user', [UserController::class, 'updateUser'])->middleware('auth:sanctum');
Route::get('admin/users', [UserController::class, 'getAdminUsers'])->middleware('auth:sanctum');
Route::delete('user/delete/{id}', [UserController::class, 'destroy']);
Route::delete('user/destroy/{id}', [UserController::class, 'destroyPermanent'])->middleware('auth:sanctum');
Route::put('user/enable/{id}', [UserController::class, 'updateStatus']);
Route::get('user/restore/{id}', [UserController::class, 'restoreUser']);
Route::post('user/add/course', [UserController::class, 'addUserCourse']);
Route::delete('user/delete/{courseId}/{userId}', [UserController::class, 'removeUserCourse']);
Route::get('user/courses/{id}', [UserController::class, 'listUserCourses'])->middleware('auth:sanctum');
Route::get('user/select/courses/{id}', [UserController::class, 'listCourses'])->middleware('auth:sanctum');


//program
Route::get('programs', [ProgramController::class, 'index']);
Route::get('program/{id}', [ProgramController::class, 'getProgramById']);
Route::get('admin/programs', [ProgramController::class, 'getAdminPrograms'])->middleware('auth:sanctum'); // can be achived or not
Route::post('program/edit/{id}', [ProgramController::class, 'update']);
Route::put('program/enable/{id}', [ProgramController::class, 'updateStatus']);
Route::get('program/restore/{id}', [ProgramController::class, 'restoreProgram']); // retrieves deleted program
Route::delete('program/delete/{id}', [ProgramController::class, 'destroy']);
Route::delete('program/destroy/{id}', [ProgramController::class, 'destroyPermanent'])->middleware('auth:sanctum');
Route::post('program', [ProgramController::class, 'store'])->middleware('auth:sanctum');
Route::post('program/add/course', [ProgramController::class, 'addProgramCourse']);
Route::delete('program/delete/{courseId}/{programId}', [ProgramController::class, 'removeProgramCourse']);
Route::get('program/courses/{id}', [ProgramController::class, 'listProgramCourses'])->middleware('auth:sanctum');
Route::get('programs/courses', [ProgramController::class, 'listProgramsWithCourses']);
Route::post('program/register/{id}', [ProgramController::class, 'registerProgram'])->middleware('auth:sanctum');
Route::get('programs/enrolled', [ProgramController::class, 'enrolledPrograms'])->middleware('auth:sanctum');


//course
Route::get('courses', [CourseController::class, 'index']);
Route::get('course/{id}', [CourseController::class, 'getCourseById']);
Route::get('courses/manage', [CourseController::class, 'manageCourses'])->middleware('auth:sanctum');
Route::get('courses/manage/{programId}', [CourseController::class, 'manageCoursesEx'])->middleware('auth:sanctum');
Route::get('courses/enrolled', [CourseController::class, 'enrolledCourses'])->middleware('auth:sanctum');
Route::get('course/restore/{id}', [CourseController::class, 'restoreCourse']); // retrieves deleted course
Route::post('course', [CourseController::class, 'store'])->middleware('auth:sanctum');
Route::post('course/edit/{id}', [CourseController::class, 'update'])->middleware('auth:sanctum');
Route::put('course/enable/{id}', [CourseController::class, 'updateStatus'])->middleware('auth:sanctum');
Route::put('course/completed/{id}', [CourseController::class, 'markCompleted'])->middleware('auth:sanctum');
Route::delete('course/delete/{id}', [CourseController::class, 'destroy'])->middleware('auth:sanctum');
Route::delete('course/destroy/{id}', [CourseController::class, 'destroyPermanent'])->middleware('auth:sanctum');
Route::post('course/add/module', [CourseController::class, 'addCourseModule'])->middleware('auth:sanctum');
Route::delete('course/delete/{courseId}/{moduleId}', [CourseController::class, 'removeCourseModule'])->middleware('auth:sanctum');
Route::get('course/modules/{id}', [CourseController::class, 'listCourseModules']);
Route::post('course/register/{id}', [CourseController::class, 'registerCourse'])->middleware('auth:sanctum');

//module
Route::get('modules', [ModuleController::class, 'index']);
Route::get('study/modules/{id}', [ModuleController::class, 'listCourseModules'])->middleware('auth:sanctum');
Route::get('module/{id}', [ModuleController::class, 'getModuleById']);
Route::get('modules/manage', [ModuleController::class, 'manageModules'])->middleware('auth:sanctum');
Route::get('modules/manage/{courseId}', [ModuleController::class, 'manageModulesEx'])->middleware('auth:sanctum');
Route::post('module', [ModuleController::class, 'store'])->middleware('auth:sanctum');
Route::post('module/edit/{id}', [ModuleController::class, 'update'])->middleware('auth:sanctum');
Route::delete('module/delete/{id}', [ModuleController::class, 'destroy'])->middleware('auth:sanctum');
Route::delete('module/destroy/{id}', [ModuleController::class, 'destroyPermanent'])->middleware('auth:sanctum');
Route::get('module/restore/{id}', [ModuleController::class, 'restoreModule']);
Route::get('module/contents/{id}', [ModuleController::class, 'getModuleContents'])->middleware('auth:sanctum'); // to be done
Route::get('study/module/{id}', [ModuleController::class, 'getStudyModuleContents'])->middleware('auth:sanctum'); // to be done

//content
Route::get('content/{id}', [ContentController::class, 'getContentById']);
Route::post('content', [ContentController::class, 'store']);
Route::delete('content/delete/{id}', [ContentController::class, 'destroy']);
Route::post('content/video/edit/{id}', [ContentController::class, 'updateVideo']);
Route::post('content/image/edit/{id}', [ContentController::class, 'updateImage']);
Route::post('content/document/edit/{id}', [ContentController::class, 'updateDocument']);
Route::get('content/document/{id}', [ContentController::class, 'getDocument']);
Route::get('content/image/{id}', [ContentController::class, 'getImage']);
Route::get('content/video/{id}', [ContentController::class, 'getVideo']);
Route::get('content/text/{id}', [ContentController::class, 'getText']);
Route::put('content/text/edit/{id}', [ContentController::class, 'updateText']);
Route::put('content/quize/edit/{id}', [ContentController::class, 'updateQuize']);

// quize
Route::post('question/{id}', [QuizeController::class, 'createQuestion'])->middleware('auth:sanctum');
Route::post('option/{id}', [QuizeController::class, 'createOption'])->middleware('auth:sanctum');
Route::post('quize/question/{id}', [QuizeController::class, 'updateQuestion'])->middleware('auth:sanctum');
Route::post('quize/option/{id}', [QuizeController::class, 'updateOption'])->middleware('auth:sanctum');
Route::delete('quize/question/{id}', [QuizeController::class, 'deleteQuestion'])->middleware('auth:sanctum');
Route::delete('quize/option/{id}', [QuizeController::class, 'deleteOption'])->middleware('auth:sanctum');
Route::get('quize/questions/{id}', [QuizeController::class, 'index'])->middleware('auth:sanctum');
Route::get('question/{id}', [QuizeController::class, 'getQuestion'])->middleware('auth:sanctum');
Route::post('quize/marks/{id}', [QuizeController::class, 'registerMarkObtained'])->middleware('auth:sanctum');

// Discussions
Route::get('discussion/{id}', [PostController::class, 'index'])->middleware('auth:sanctum');
Route::get('post/messages/{id}', [PostController::class, 'getPostMessages'])->middleware('auth:sanctum');
Route::post('discussion/post/{id}', [PostController::class, 'store'])->middleware('auth:sanctum');
Route::delete('discussion/post/{id}', [PostController::class, 'destroy'])->middleware('auth:sanctum');
// Messages
Route::get('message/chat/{id}', [MessageController::class, 'index'])->middleware('auth:sanctum');
Route::get('message/chats', [MessageController::class, 'getChats'])->middleware('auth:sanctum');
Route::post('message/send', [MessageController::class, 'store'])->middleware('auth:sanctum');
Route::delete('message/{id}', [MessageController::class, 'destroy'])->middleware('auth:sanctum');
Route::delete('chat/{id}', [MessageController::class, 'destroyChat'])->middleware('auth:sanctum');
Route::post('message/like/{id}', [MessageController::class, 'likeMessage'])->middleware('auth:sanctum');
Route::post('message/unlike/{id}', [MessageController::class, 'removeLike'])->middleware('auth:sanctum');
