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

        $quize = Quize::with('content', 'questions.options') // Load relationships
    ->findOrFail($quizeId);

        foreach ($quize->questions as $question) {
            $question->imageUrl = asset('storage/' . substr($question->imageUrl, 7));
            
            foreach($question->options as $option){
                if($option->type=="image"){
                $option->data = asset('storage/' . substr($option->data, 7));
                }
            }
        }

        $quize->title=$quize->content->title;
        $data = [
            'status' => 200,
            'quize' => $quize
        ];
        return response()->json($data, 200);
    }


    public function getQuestion($questionId)
    {

        $question = Question::with('options') // Load relationships
            ->findOrFail($questionId);

        if($question->imageUrl!=null)
            $question->imageUrl = asset('storage/' . substr($question->imageUrl, 7));
        else
            $question->imageUrl = "";

        foreach($question->options as $option){
            if($option->type=="image"){
                $option->data = asset('storage/' . substr($option->data, 7));
            }
        }

        $data = [
            'status' => 200,
            'question' => $question
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
            
                // create question and choices
                $question = new Question();

                if ($request->hasFile('imageUrl')) {
                    $path = $request->file('imageUrl')->store('public/images');
                    $question->imageUrl = $path;
                }
                $question->quize_id = $id;
                $question->context = $request->context;
                $question->save();

                $data = [
                    'status' => 200,
                    'message' => 'Question successfully created!',
                    'id' => $question->id
                ];
                return response()->json($data, 200);
            
        }
    }

    // Create question
    public function updateQuestion(Request $request, $id)
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
            
                $question = Question::find($id);

                if ($request->hasFile('imageUrl')) {
                    if ($question->imageUrl != null) {
                        Storage::delete($question->imageUrl);
                    }
                    $path = $request->file('imageUrl')->store('public/images');
                    $question->imageUrl = $path;
                }
                $question->context = $request->context;
                $question->save();

                $data = [
                    'status' => 200,
                    'message' => 'Question successfully updated!'
                ];
                return response()->json($data, 200);
        }
    }

    // Create option
    public function createOption(Request $request, $id)
    {
            // create option
            $option = new Option();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('public/images');
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
            $option->weight = 0;
            if( $request->isCorrect)
                $option->weight = 1;
            $option->save();

            $data = [
                'status' => 200,
                'message' => 'Option successfully created!'
            ];
            return response()->json($data, 200);
        
    }

    // Update option
    public function updateOption(Request $request, $id)
    {
        
            // find option
            $option = Option::find($id);
            if ($request->hasFile('data')) {
                Storage::delete($option->data);
                $path = $request->file('data')->store('public/images');
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