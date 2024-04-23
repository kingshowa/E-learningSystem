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

    // Get document
    public function getDocument($id)
    {
        $document = Document::find($id);
        $content = Content::find($document->content_id);

        if ($document == null) {
            $data = [
                'status' => 400,
                'message' => 'Content not found'
            ];
            return response()->json($data, 400);
        } else {
            $document->title=$content->title;
            $document->link = asset('storage/' . substr($document->link, 7));

            $data = [
                'status' => 200,
                'document' => $document
            ];
            return response()->json($data, 200);
        }
    }

    // Get document
    public function getImage($id)
    {
        $image = Image::find($id);
        $content = Content::find($image->content_id);

        if ($image == null) {
            $data = [
                'status' => 400,
                'message' => 'Content not found'
            ];
            return response()->json($data, 400);
        } else {
            $image->title=$content->title;
            $image->link = asset('storage/' . substr($image->link, 7));

            $data = [
                'status' => 200,
                'image' => $image
            ];
            return response()->json($data, 200);
        }
    }

    // Get video
    public function getVideo($id)
    {
        $video = Video::find($id);
        $content = Content::find($video->content_id);

        if ($video == null) {
            $data = [
                'status' => 400,
                'message' => 'Content not found'
            ];
            return response()->json($data, 400);
        } else {
            $video->title=$content->title;
            $video->link = asset('storage/' . substr($video->link, 7));

            $data = [
                'status' => 200,
                'video' => $video
            ];
            return response()->json($data, 200);
        }
    }

    // Get document
    public function getText($id)
    {
        $text = Text::find($id);
        $content = Content::find($text->content_id);

        if ($text == null) {
            $data = [
                'status' => 400,
                'message' => 'Content not found'
            ];
            return response()->json($data, 400);
        } else {
            $text->title=$content->title;
            $data = [
                'status' => 200,
                'text' => $text
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
            $content->duration = $request->duration;
            $content->save();

            $contentId = $content->id;

            if ($request->type == 'video') { // create video
                $video = new Video();

                // when it is an uploaded file
                if ($request->hasFile('video')) { // must put a field name='videoFile'

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
                        $path = $request->file('video')->store('public/videos');
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
                    $path = $request->file('image')->store('public/images');
                    $image->link = $path;
                }
                $image->content_id = $contentId;
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
                    $path = $request->file('document')->store('public/documents');
                    $document->link = $path;
                }
                $document->content_id = $contentId;
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
        $content = Content::find($video->content_id);

        if ($video == null) {
            $data = [
                'status' => 404,
                'message' => 'Content not found!'
            ];
            return response()->json($data, 404);
        } else {

            // when it is an uploaded file
            if ($request->hasFile('video')) { 
                
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
                    Storage::delete($video->link);
                    $path = $request->file('video')->store('public/videos');
                    $video->uploaded = true;
                    $video->link = $path;
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
            
            $video->caption = $request->caption;
            $video->start = $request->start;
            $video->end = $request->end;
            $video->save();

            $content->title=$request->title;
            $content->save();

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
        $content = Content::find($image->content_id);

        if ($image == null) {
            $data = [
                'status' => 404,
                'message' => 'Content not found!'
            ];
            return response()->json($data, 404);
        } else {
            if($request->hasFile('image')){
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
                Storage::delete($image->link);
                $path = $request->file('image')->store('public/images');
                $image->link = $path;
            }

            }
            
            $image->caption = $request->caption;
            $image->save();

            $content->title=$request->title;
            $content->save();

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
        $content = Content::find($document->content_id);

        if ($document == null) {
            $data = [
                'status' => 404,
                'message' => 'Content not found!'
            ];
            return response()->json($data, 404);
        } else {
            if($request->hasFile('document')){

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
                    Storage::delete($document->link);

                    $path = $request->file('document')->store('public/documents');
                    $document->link = $path;
                }
            }
            
            $document->caption = $request->caption;
            $document->save();

            $content->title=$request->title;
            $content->save();
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
        $content = Content::find($text->content_id);

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

                $content->title=$request->title;
                $content->save();

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
        $content = Content::find($request->content_id);

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

                $content->title=$request->title;
                $content->save();

                $data = [
                    'status' => 200,
                    'message' => 'Quiz updated!'
                ];
                return response()->json($data, 200);
            }
        }
    }
}