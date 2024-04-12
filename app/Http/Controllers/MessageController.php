<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Like;
use Illuminate\Support\Facades\Storage;
use Validator;

class MessageController extends Controller
{
    private $user;

    public function getUser(){
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

    // List all the messages in a particular chat between 2 users
    public function index($id)
    {
        $receiver = User::find($id);
        if (!$receiver) {
            $data = [
                'status' => 404,
                'message' => 'Parameter error!'
            ];
            return response()->json($data, 404);
        }

        

        $messages = Message::whereRaw(
            '(sent_by = ? AND sent_to = ?) OR (sent_by = ? AND sent_to = ?)',
            [$receiver->id, $this->getUser(), $this->getUser(), $receiver->id]
        )->get();

        $data = [
            'status' => 200,
            'messages' => $messages,
        ];
        return response()->json($data, 200);
    }

    // Create new message
    public function store(Request $request)
    {
        $message = new Message();
        if (isset ($request->post_id)) {
            $post = Post::find($request->post_id);
            if (!$post) {
                $data = [
                    'status' => 404,
                    'message' => 'Parameter error!'
                ];
                return response()->json($data, 404);
            } else {
                $message->post_id = $post->id;
            }
        }

        if (isset ($request->sent_to)) {
            $receiver = User::find($request->sent_to);
            if (!$receiver) {
                $data = [
                    'status' => 404,
                    'message' => 'Parameter error!'
                ];
                return response()->json($data, 404);
            } else {
                $message->sent_to = $receiver->id;
            }
        }
        
        $message->sent_by = $this->getUser();
        $message->text = $request->text;
        if (isset ($request->attachment) && $request->attachment) {
            $validator = Validator::make($request->all(), [
                'attachment' => 'mimes:pdf,docx,xlsx,txt,pptx,xml,html,jpeg,jpg,png,tiff,zip,rar|max:10000',
            ]);

            if ($validator->fails()) {
                $data = [
                    'status' => 422,
                    'message' => $validator->messages()
                ];
                return response()->json($data, 422);
            }
            $message->attachment = $request->file('attachment')->store('attachments');
        }
        $message->save();
        $data = [
            'status' => 200,
            'message' => 'Message sent!'
        ];
        return response()->json($data, 200);
    }

    // Delete message
    public function destroy($id)
    {
        $message = Message::find($id);
        if (!$message) {
            $data = [
                'status' => 404,
                'message' => 'Messge not Found'
            ];
            return response()->json($data, 404);
        } else {
            if ($message->attachment != null) {
                Storage::delete($message->attachment);
            }
            $message->delete();
            $data = [
                'status' => 200,
                'message' => 'Deleted'
            ];
            return response()->json($data, 200);
        }
    }

    // like message
    public function likeMessage($id)
    {
        $message = Message::find($id);
        if (!$message) {
            $data = [
                'status' => 404,
                'message' => 'Not found!'
            ];
            return response()->json($data, 404);
        } else {
            $like = new Like();

            $like->user_id = $this->getUser();
            $like->message_id = $message->id;
            $like->save();
            $data = [
                'status' => 200,
                'message' => 'Liked'
            ];
            return response()->json($data, 200);
        }
    }

    // Delete like
    public function removeLike($messageId)
    {
        $like = Like::where('user_id', $this->getUser())->where('message_id', $messageId)->first();
        if (!$like) {
            $data = [
                'status' => 404,
                'message' => 'Like not Found'
            ];
            return response()->json($data, 404);
        } else {
            $like->delete();
            $data = [
                'status' => 200,
                'message' => 'Deleted'
            ];
            return response()->json($data, 200);
        }
    }
}
