<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\Video;
use App\Models\Image;
use App\Models\Document;
use App\Models\Text;
use App\Models\Quize;
use App\Models\Module;
use Validator;

class ContentController extends Controller
{
    // Get content by id
    public function getContentById($id)
    {
        $content = Content::find($id);

        if ($content == null) {
            $data = [
                'status' => 400,
                'message' => 'Content not found'
            ];
            return response()->json($data, 400);
        } else {
            $data = [
                'status' => 200,
                'content' => $content
            ];
            return response()->json($data, 200);
        }
    }

    // Create content
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'moduleId' => 'required',
            'type' => 'required'
        ]);

        $module = Module::find($request->moduleId); // test 

        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'message' => $validator->messages()
            ];
            return response()->json($data, 422);
        }else if($module==null) {
            $data = [
                'status' => 404,
                'message' => 'Module not found!'
            ];
            return response()->json($data, 404);
        } else {

            $content = new Content();
            $content->moduleId = $request->moduleId;
            $content->type = $request->type;
            $content->save();

            $contentId = $content->id;

            if ($request->type == 'video') { // create video
                $video = new Video();

                // when it is an uploaded file
                if ($request->has('videoFile')) { // must put a field name='videoFile'
                    
                    $validator = Validator::make($request->all(), [
                        'video' => 'required|mimes:mp4,mov,avi', //max:2048 Adjust max file size as needed
                    ]);

                    if ($validator->fails()) {
                        $data = [
                            'status' => 422,
                            'message' => $validator->messages()
                        ];
                        $content->delete();
                        return response()->json($data, 422);
                    } else {
                        $path = $request->file('video')->store('videos');
                        $video->uploaded = true;
                    }
                } else { //  when it is an external link
                    $validator = Validator::make($request->all(), [
                        'link' => 'required'
                    ]);
                    if ($validator->fails()) {
                        $data = [
                            'status' => 422,
                            'message' => $validator->messages()
                        ];
                        $content->delete();
                        return response()->json($data, 422);
                    } else {
                        $path = $request->link;
                        $video->uploaded = false;
                    }
                }
                $video->contentId = $contentId;
                $video->link = $path;
                $video->caption = $request->caption;
                $video->start = $request->start;
                $video->end = $request->end;
                $video->save();
            } else if ($request->type == 'image') { // create image
                $image = new Image();

                $validator = Validator::make($request->all(), [
                    'image' => 'required|mimes:jpeg,jpg,png,tiff|max:5000', //Adjust max file size as needed
                ]);

                if ($validator->fails()) {
                    $data = [
                        'status' => 422,
                        'message' => $validator->messages()
                    ];
                    $content->delete();
                    return response()->json($data, 422);
                } else {
                    $path = $request->file('image')->store('images');
                }
                $image->contentId = $contentId;
                $image->link = $path;
                $image->caption = $request->caption;
                $image->save();
            } else if ($request->type == 'document') { // create document
                $document = new Document();

                $validator = Validator::make($request->all(), [
                    'document' => 'required|mimes:pdf,docx,xlsx,txt,pptx,xml,html', //Adjust max file size as needed
                ]);

                if ($validator->fails()) {
                    $data = [
                        'status' => 422,
                        'message' => $validator->messages()
                    ];
                    $content->delete();
                    return response()->json($data, 422);
                } else {
                    $path = $request->file('document')->store('documents');
                }
                $document->contentId = $contentId;
                $document->link = $path;
                $document->caption = $request->caption;
                $document->save();
            } else if ($request->type == 'text') {  // create yext
                $text = new Text();
                $text->contentId = $contentId;
                $text->data = $request->data;
                $text->save();
            } else if ($request->type == 'quize') {  // create quize
                $quize = new Quize();
                $quize->contentId = $contentId;
                $quize->save();
            } else {
                $data = [
                    'status' => 401,
                    'message' => 'Error encountered!'
                ];
                return response()->json($data, 401);
            }

            $data = [
                'status' => 200,
                'message' => 'Content created successfully'
            ];
            return response()->json($data, 200);
        }
    }

    // Update module details
    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         $data = [
    //             'status' => 422,
    //             'message' => $validator->messages()
    //         ];
    //         return response()->json($data, 422);
    //     } else {
    //         $module = Module::find($id);

    //         if ($module == null) {
    //             $data = [
    //                 'status' => 421,
    //                 'message' => 'This module does not exist.'
    //             ];
    //             return response()->json($data, 421);
    //         } else {
    //             $module->name = $request->name;
    //             $module->description = $request->description;
    //             $module->code = $request->code;
    //             $module->duration = $request->duration;
    //             $module->creator = $request->creator;
    //             $module->save();
    //             $data = [
    //                 'status' => 200,
    //                 'message' => 'Module updated successfully'
    //             ];
    //             return response()->json($data, 200);
    //         }
    //     }
    // }

    // // Delete module
    // public function destroy($id)
    // {
    //     $module = Module::find($id);

    //     if ($module == null) {
    //         $data = [
    //             'status' => 421,
    //             'message' => 'This module does not exist.'
    //         ];
    //         return response()->json($data, 421);
    //     } else {
    //         $module->delete();
    //         $data = [
    //             'status' => 200,
    //             'message' => 'Module deleted successfully'
    //         ];
    //         return response()->json($data, 200);
    //     }
    // }

    // // Restore deleted course
    // public function restoreModule($id)
    // {
    //     $module = Module::onlyTrashed()->find($id);
    //     if ($module != null) {
    //         $module->restore();
    //         $data = [
    //             'status' => 200,
    //             'message' => 'Module successfully restored'
    //         ];
    //         return response()->json($data, 200);
    //     } else {
    //         $data = [
    //             'status' => 400,
    //             'message' => 'Module not found'
    //         ];
    //         return response()->json($data, 400);
    //     }
    // }
}
