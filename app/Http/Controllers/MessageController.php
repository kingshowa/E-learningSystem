<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Like;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Facades\Auth;


class MessageController extends Controller
{
    private $user;

    public function getUser()
    {
        $u = Auth::user();

        if ($u) {
            $this->user = $u->id;
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

        foreach ($messages as $message) {
            if ($message->attachment != null) {
                $message->attachment = asset('storage/' . substr($message->attachment, 7));
            }
        }

        $receiver->messages = $messages;
        $receiver->photo = asset('storage/' . substr($receiver->photo, 7));

        $data = [
            'status' => 200,
            'chat' => $receiver,
        ];
        return response()->json($data, 200);
    }



    // List all the chats I had befor
    public function getChats()
    {
        $userId = $this->getUser();

        // Retrieve distinct users involved in conversations (both senders and recipients)
        $userIdsSentTo = DB::table('messages')
            ->select('sent_to as user_id')
            ->where('sent_by', $userId)
            ->whereNotNull('sent_to') // Ensure sent_to is not NULL for individual conversations
            ->groupBy('sent_to');

        $userIdsSentBy = DB::table('messages')
            ->select('sent_by as user_id')
            ->where('sent_to', $userId)
            ->whereNotNull('sent_to') // Ensure sent_to is not NULL for individual conversations
            ->groupBy('sent_by');

        // Combine and retrieve all distinct user IDs involved in conversations
        $userIds = DB::table(DB::raw("({$userIdsSentTo->toSql()} UNION ALL {$userIdsSentBy->toSql()}) as user_conversations"))
            ->mergeBindings($userIdsSentTo)
            ->mergeBindings($userIdsSentBy)
            ->groupBy('user_id')
            ->pluck('user_id')
            ->reject(function ($sentBy) use ($userId) {
                return $sentBy == $userId; // Exclude the current user's ID
            })
            ->toArray();

        // Retrieve users based on the extracted IDs
        $users = User::whereIn('id', $userIds)->get();

        $i = 0;
        foreach ($users as $user) {
            $u = $user->id;
            $lastMessage = Message::select('text', 'sent_by')
                ->where(function ($query) use ($userId, $u) {
                    $query->where('sent_by', $userId)
                        ->where('sent_to', $u)
                        ->orWhere('sent_by', $u)
                        ->where('sent_to', $userId);
                })
                ->orderBy('id', 'desc') // Order by message ID in descending order to get the latest message
                ->first();

            $users[$i]->lastMessage = $lastMessage;
            if ($users[$i]->photo)
                $users[$i]->photo = asset('storage/' . substr($users[$i]->photo, 7));

            $i++;
        }

        $data = [
            'status' => 200,
            'chats' => $users,
        ];

        return response()->json($data, 200);
    }

    // Create new message
    public function store(Request $request)
    {
        $message = new Message();
        if (isset($request->post_id)) {
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

        if (isset($request->sent_to)) {
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
        if ($request->hasFile('attachment')) {
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
            $message->attachment = $request->file('attachment')->store('public/attachments');
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
