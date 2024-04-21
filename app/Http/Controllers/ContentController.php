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
use Illuminate\Support\Facades\Storage;

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
        } else if ($module == null) {
            $data = [
                'status' => 404,
                'message' => 'Module not found!'
            ];
            return response()->json($data, 404);
        } else {

            $content = new Content();
            $content->module_id = $request->moduleId;
            $content->type = $request->type;
            $content->title = $request->title;
            $content->title = $request->title;
            $content->duration = $request->duration;
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
                $video->content_id = $contentId;
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
                $image->content_id = $contentId;
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
                $document->content_id = $contentId;
                $document->link = $path;
                $document->caption = $request->caption;
                $document->save();
            } else if ($request->type == 'text') {  // create yext
                $text = new Text();
                $text->content_id = $contentId;
                $text->data = $request->data;
                $text->save();
            } else if ($request->type == 'quize') {  // create quize
                $validator = Validator::make($request->all(), [
                    'instruction' => 'required',
                    'pass_percentage' => 'required'
                ]);

                if ($validator->fails()) {
                    $data = [
                        'status' => 422,
                        'message' => $validator->messages()
                    ];
                    return response()->json($data, 422);
                } else {
                    $quize = new Quize();
                    $quize->content_id = $contentId;
                    $quize->pass_percentage = $request->pass_percentage;
                    $quize->instruction = $request->instruction;
                    $quize->save();
                }
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

    // Delete content
    public function destroy($id)
    {
        $content = Content::find($id);

        if ($content == null) {
            $data = [
                'status' => 421,
                'message' => 'This content does not exist.'
            ];
            return response()->json($data, 421);
        } else {
            if ($content->type == 'video') {
                $video = Video::select('*')->where('content_id', $content->id)->first();
                if ($video->uploaded == 1) {
                    // Delete the old video file from storage
                    Storage::delete($video->link);
                }
            }
            if ($content->type == 'image') {
                $image = Image::select('*')->where('content_id', $content->id)->first();
                Storage::delete($image->link);
            }
            if ($content->type == 'document') {
                $document = Document::select('*')->where('content_id', $content->id)->first();
                Storage::delete($document->link);
            }
            $content->delete();
            $data = [
                'status' => 200,
                'message' => 'Content deleted successfully'
            ];
            return response()->json($data, 200);
        }
    }

    // Update video content
    public function updateVideo(Request $request, $id)
    {
        $video = Video::find($id);
        if ($video == null) {
            $data = [
                'status' => 404,
                'message' => 'Content not found!'
            ];
            return response()->json($data, 404);
        } else {
            if ($video->uploaded == 1) {
                // Delete the old video file from storage
                Storage::delete($video->link);
            }

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
                        'status' => 423,
                        'message' => $validator->messages()
                    ];
                    return response()->json($data, 423);
                } else {
                    $path = $request->link;
                    $video->uploaded = false;
                }
            }
            $video->link = $path;
            $video->caption = $request->caption;
            $video->start = $request->start;
            $video->end = $request->end;
            $video->save();

            $data = [
                'status' => 200,
                'message' => 'Video updated!'
            ];
            return response()->json($data, 200);
        }
    }

    // Update image
    public function updateImage(Request $request, $id)
    {
        $image = Image::find($id);

        if ($image == null) {
            $data = [
                'status' => 404,
                'message' => 'Content not found!'
            ];
            return response()->json($data, 404);
        } else {
            Storage::delete($image->link);
            $validator = Validator::make($request->all(), [
                'image' => 'required|mimes:jpeg,jpg,png,tiff|max:5000', //Adjust max file size as needed
            ]);

            if ($validator->fails()) {
                $data = [
                    'status' => 422,
                    'message' => $validator->messages()
                ];
                return response()->json($data, 422);
            } else {
                $path = $request->file('image')->store('images');
            }
            $image->link = $path;
            $image->caption = $request->caption;
            $image->save();

            $data = [
                'status' => 200,
                'message' => 'Image updated!'
            ];
            return response()->json($data, 200);
        }
    }

    // Update document
    public function updateDocument(Request $request, $id)
    {
        $document = Document::find($id);

        if ($document == null) {
            $data = [
                'status' => 404,
                'message' => 'Content not found!'
            ];
            return response()->json($data, 404);
        } else {
            Storage::delete($document->link);

            $validator = Validator::make($request->all(), [
                'document' => 'required|mimes:pdf,docx,xlsx,txt,pptx,xml,html', //Adjust max file size as needed
            ]);

            if ($validator->fails()) {
                $data = [
                    'status' => 422,
                    'message' => $validator->messages()
                ];
                return response()->json($data, 422);
            } else {
                $path = $request->file('document')->store('documents');
            }
            $document->link = $path;
            $document->caption = $request->caption;
            $document->save();

            $data = [
                'status' => 200,
                'message' => 'Document updated!'
            ];
            return response()->json($data, 200);
        }
    }

    // Update text  content
    public function updateText(Request $request, $id)
    {
        $text = Text::find($id);

        if ($text == null) {
            $data = [
                'status' => 404,
                'message' => 'Content not found!'
            ];
            return response()->json($data, 404);
        } else {
            $validator = Validator::make($request->all(), [
                'data' => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status' => 422,
                    'message' => $validator->messages()
                ];
                return response()->json($data, 422);
            } else {
                $text->data = $request->data;
                $text->save();

                $data = [
                    'status' => 200,
                    'message' => 'Text updated!'
                ];
                return response()->json($data, 200);
            }
        }
    }

    // Update quize  content
    public function updateQuize(Request $request, $id)
    {
        $quize = Quize::find($id);

        if ($quize == null) {
            $data = [
                'status' => 404,
                'message' => 'Content not found!'
            ];
            return response()->json($data, 404);
        } else {

            $validator = Validator::make($request->all(), [
                'instruction' => 'required',
                'pass_percentage' => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status' => 422,
                    'message' => $validator->messages()
                ];
                return response()->json($data, 422);
            } else {
                $quize->pass_percentage = $request->pass_percentage;
                $quize->instruction = $request->instruction;
                $quize->save();

                $data = [
                    'status' => 200,
                    'message' => 'Quiz updated!'
                ];
                return response()->json($data, 200);
            }
        }
    }
}