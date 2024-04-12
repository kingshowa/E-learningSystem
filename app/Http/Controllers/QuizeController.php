<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\Option;
use App\Models\Quize;
use Illuminate\Support\Facades\Storage;
use Validator;

class QuizeController extends Controller
{

    private $user;

    public function getUser()
    {
        if (isset ($_SESSION['admin'])) {
            $this->user = $_SESSION['admin'];
        } else if (isset ($_SESSION['teacher'])) {
            $this->user = $_SESSION['teacher'];
        } else if (isset ($_SESSION['student'])) {
            $this->user = $_SESSION['student'];
        } else {
            $data = [
                'status' => 400,
                'message' => 'User not connected'
            ];
            return response()->json($data, 400);
        }
        return $this->user;
    }


    public function index($quizeId)
    {

        $quize = Quize::with('questions.options')->findOrFail($quizeId);

        $data = [
            'status' => 200,
            'questions' => $quize
        ];
        return response()->json($data, 200);
    }
    // Create question
    public function createQuestion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "context" => "required",
        ]);
        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'message' => $validator->messages()
            ];
            return response()->json($data, 422);
        } else {
            $quize = Quize::find($id);
            if (!$quize) {
                $data = [
                    'status' => 404,
                    'message' => 'Parameter error!'
                ];
                return response()->json($data, 404);
            } else {
                // create question and choices
                $question = new Question();

                if ($request->has('image')) {
                    $path = $request->file('image')->store('images');
                    $question->imageUrl = $path;
                }
                $question->quize_id = $id;
                $question->context = $request->context;
                $question->save();

                $data = [
                    'status' => 200,
                    'message' => 'Question successfully created!',
                    'questionId' => $question->id
                ];
                return response()->json($data, 200);
            }
        }
    }

    // Create question
    public function updateQuestion(Request $request, $id, $questionId)
    {
        $validator = Validator::make($request->all(), [
            "context" => "required",
        ]);
        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'message' => $validator->messages()
            ];
            return response()->json($data, 422);
        } else {
            $quize = Quize::find($id);
            if (!$quize) {
                $data = [
                    'status' => 404,
                    'message' => 'Parameter error!'
                ];
                return response()->json($data, 404);
            } else {
                // create question and choices
                $question = Question::find($questionId);

                if ($request->has('image')) {
                    if ($question->imageUrl != null) {
                        Storage::delete($question->imageUrl);
                    }
                    $path = $request->file('image')->store('images');
                    $question->imageUrl = $path;
                }
                $question->context = $request->context;
                $question->save();

                $data = [
                    'status' => 200,
                    'message' => 'Question successfully updated!',
                    'question_id' => $question->id
                ];
                return response()->json($data, 200);
            }
        }
    }

    // Create option
    public function createOption(Request $request, $id)
    {
        $question = Question::find($id);
        if (!$question) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        } else {
            // create option
            $option = new Option();

            if ($request->has('image')) {
                $path = $request->file('image')->store('images');
                $option->data = $path;
                $option->type = 'image';
            } else {
                $validator = Validator::make($request->all(), [
                    "data" => "required"
                ]);
                if ($validator->fails()) {
                    $data = [
                        'status' => 422,
                        'message' => $validator->messages()
                    ];
                    return response()->json($data, 422);
                } else {
                    $option->data = $request->data;
                    $option->type = 'text';
                }
            }
            $option->question_id = $id;
            $option->isCorrect = $request->isCorrect;
            $option->weight = $request->weight;
            $option->save();

            $data = [
                'status' => 200,
                'message' => 'Option successfully created!'
            ];
            return response()->json($data, 200);
        }
    }

    // Update option
    public function updateOption(Request $request, $id, $optionId)
    {
        $question = Question::find($id);
        if (!$question) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        } else {
            // find option
            $option = Option::find($optionId);
            if ($option->type == "image") {
                Storage::delete($option->data);
            }
            if ($request->has('image')) {
                $path = $request->file('image')->store('images');
                $option->data = $path;
                $option->type = 'image';
            } else {
                $validator = Validator::make($request->all(), [
                    "data" => "required"
                ]);
                if ($validator->fails()) {
                    $data = [
                        'status' => 422,
                        'message' => $validator->messages()
                    ];
                    return response()->json($data, 422);
                } else {
                    $option->data = $request->data;
                    $option->type = 'text';
                }
            }
            $option->isCorrect = $request->isCorrect;
            $option->weight = $request->weight;
            $option->save();

            $data = [
                'status' => 200,
                'message' => 'Option successfully updated!'
            ];
            return response()->json($data, 200);
        }
    }

    // delete quetion
    public function deleteQuestion($id)
    {
        $question = Question::find($id);
        if (!$question) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        } else {
            if ($question->imageUrl != null) {
                Storage::delete($question->imageUrl);
            }
            $options = Option::where('question_id', '=', $question->id)->get();
            foreach ($options as $option) {
                if ($option->type == 'image') {
                    Storage::delete($option->data);
                }
            }
            $question->delete();
            $data = [
                'status' => 200,
                'message' => 'Deleted.'
            ];
            return response()->json($data, 200);
        }
    }

    // delete option
    public function deleteOption($id)
    {
        $option = Option::find($id);
        if (!$option) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        } else {
            if ($option->type == 'image') {
                Storage::delete($option->data);
            }
            $option->delete();
            $data = [
                'status' => 200,
                'message' => 'Deleted.'
            ];
            return response()->json($data, 200);
        }
    }

    // Register marks obtained by user on a partucular quize
    public function registerMarkObtained(Request $request, $id)
    {   
        Quize::findOrFail($id);
        $mark = Mark::where('user_id', '=', $this->getUser())->where('quize_id', '=', $id)->first();
        if (!$mark) {
            $mark = new Mark();
            $mark->user_id = $this->getUser();
            $mark->quize_id = $id;
            $mark->mark_obtained = $request->mark_obtained;
            $mark->attempts = 1;
            $mark->save();
            $data = [
                'status' => 200,
                'message' => 'Mark Saved'
            ];
            return response()->json($data, 200);
        } else {
            $mark->mark_obtained = $request->mark_obtained;
            $mark->attempts = $mark->attempts + 1;
            $mark->save();
            $data = [
                'status' => 200,
                'message' => 'Mark updated'
            ];
            return response()->json($data, 200);
        }

    }
}