<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use Validator;
class ProgramController extends Controller
{
    public function index(){
        $programs=Program::all();

        $data=[
            'status'=>200,
            'programs'=>$programs
        ];

        return response()->json($data,200);
    }

    public function store(Request $request){

        $validator=Validator::make($request->all(),[
            'name'=>'required',
            'description'=>'required'
        ]);

        if($validator->fails()){
            $data=[
                'status'=>422,
                'message'=>$validator->messages()
            ];

            return response()->json($data,422);
        }
        else{
            $program=new Program;

            $program->name=$request->name;
            $program->description=$request->description;
            $program->price=$request->price;
            $program->photo=$request->photo;
            $program->creator=$request->creator;
            $program->enabled=$request->enabled;

            $program->save();

            $data=[
                'status'=>200,
                'message'=>'Program created successfully'
            ];

            return response()->json($data,200);
        }
    }

    public function edit(Request $request, $id){

        $validator=Validator::make($request->all(),[
            'name'=>'required',
            'description'=>'required'
        ]);

        if($validator->fails()){
            $data=[
                'status'=>422,
                'message'=>$validator->messages()
            ];

            return response()->json($data,422);
        }
        else{
            $program=Program::find($id);

            $program->name=$request->name;
            $program->description=$request->description;
            $program->price=$request->price;
            $program->photo=$request->photo;
            $program->creator=$request->creator;
            $program->enabled=$request->enabled;

            $program->save();

            $data=[
                'status'=>200,
                'message'=>'Program updated successfully'
            ];

            return response()->json($data,200);
        }
    }

    public function delete($id){
        $program=Program::find($id);
        $program->delete();

        $data=[
            'status'=>200,
            'message'=>'Program deleted successfully'
        ];

        return response()->json($data,200);
    }
}
