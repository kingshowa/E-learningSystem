<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Discussion;
use Illuminate\Support\Facades\Auth;
use Validator;

class PostController extends Controller
{
    // List all the posts in a particular forum
    public function index($discussionId)
    {
        $discussion = Discussion::with([
            'posts' => function ($query) {
                $query->with([
                    'messages' => function ($query) {
                        $query->withCount('likes as total_likes');
                    }
                ]);
            }
        ])->findOrFail($discussionId);

        $data = [
            'status' => 200,
            'discussion' => $discussion
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
            $discussion = Discussion::find($id);
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
                $post->discussion_id = $id;
                $post->topic = $request->topic;
                $post->created_by = $user;
                $post->save();

                $data = [
                    'status' => 200,
                    'message' => 'Post successfully created!'
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
