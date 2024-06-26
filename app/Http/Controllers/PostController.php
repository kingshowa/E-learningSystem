<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Validator;

class PostController extends Controller
{
    // // List all the posts in a particular forum
    // public function index($courseId)
    // {
    //     $discussion = Discussion::with([
    //         'posts' => function ($query) {
    //             $query->with([
    //                 'messages' => function ($query) {
    //                     $query->withCount('likes as total_likes');
    //                 }
    //             ]);
    //         }
    //     ])->findOrFail($courseId);

    //     $data = [
    //         'status' => 200,
    //         'discussion' => $discussion
    //     ];
    //     return response()->json($data, 200);
    // }

    // get posts within a course
    public function index($courseId)
    {
        $discussion = Course::with(['posts' => function ($query) {
            $query->orderByDesc('id')->with('user');
        }])->find($courseId);
        foreach ($discussion->posts as $post){
            $user=$post['user'];
            $photo=$user['photo'];
            $post->user['photo1'] = asset('storage/' . substr($photo, 7));
        }
        $user = Auth::user()->id;
        $data = [
            'status' => 200,
            'discussion' => $discussion,
            'user' => $user,
        ];
        return response()->json($data, 200);
    }

    // get a post with its messages
    public function getPostMessages($postId)
    {
        $post = Post::with([
            'user',
            'messages' => function ($query) {
                $query->withCount('likes as total_likes');
                $query->with('sender');
            }])->find($postId);
            
            foreach ($post->messages as $message){
                $user=$message['sender'];
                $photo=$user['photo'];
                $message->sender['photo1'] = asset('storage/' . substr($photo, 7));
                if ($message->attachment != null) {
                    $message->attachment = asset('storage/' . substr($message->attachment, 7));
                }
            }

        $data = [
            'status' => 200,
            'post' => $post,
        ];
        return response()->json($data, 200);
    }

    // Create new post
    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "topic" => "required",
        ]);
        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'message' => $validator->messages()
            ];
            return response()->json($data, 422);
        } else {
            $discussion = Course::find($id);
            if (!$discussion) {
                $data = [
                    'status' => 404,
                    'message' => 'Parameter error!'
                ];
                return response()->json($data, 404);
            } else {
                $u = Auth::user();
                if ($u) {
                    $user = $u->id;
                } else {
                    $data = [
                        'status' => 400,
                        'message' => 'User not connected'
                    ];
                    return response()->json($data, 400);
                }
                // create post
                $post = new Post();
                $post->course_id = $id;
                $post->topic = $request->topic;
                $post->created_by = $user;
                $post->save();

                $data = [
                    'status' => 200,
                    'message' => 'Post successfully created!',
                    'post_id' => $post->id,
                ];
                return response()->json($data, 200);
            }
        }
    }

    // Delete post
    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            $data = [
                'status' => 404,
                'message' => 'Post not Found'
            ];
            return response()->json($data, 404);
        } else {
            $post->delete();
            $data = [
                'status' => 200,
                'message' => 'Deleted'
            ];
            return response()->json($data, 200);
        }
    }
}
