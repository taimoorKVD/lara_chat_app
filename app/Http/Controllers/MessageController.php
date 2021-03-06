<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Message;
use App\Models\MessageGroup;

use App\Events\PrivateMessageEvent;
use App\Events\GroupMessageEvent;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function conversation($userId) {
        $users = User::where('id', '!=', auth()->user()->id)->orderBy('created_at', 'DESC')->paginate(10);
        $friendInfo = User::findOrFail($userId);
        $myInfo = User::findOrFail(auth()->user()->id);

        $this->data['users'] = $users;
        $this->data['friendInfo'] = $friendInfo;
        $this->data['myInfo'] = $myInfo;
        $this->data['groups'] = MessageGroup::paginate(10);

        return view('message.conversation', $this->data);
    }

    public function sendMessage(Request $request) {
        $request->validate([
            'message' => 'required',
            'receiver_id' => 'required'
        ]);

        $sender_id = auth()->user()->id;
        $receiver_id = $request->receiver_id;

        $message = new Message();
        $message->message = $request->message;

        if($message->save()) {
            try {
                $message->users()->attach($sender_id, [ 'receiver_id' => $receiver_id]);
                $sender = User::where('id', '!=', $sender_id)->first();

                $data = [];
                $data['sender_id'] = $sender_id;
                $data['sender_name'] = $sender->name;
                $data['receiver_id'] = $receiver_id;
                $data['content'] = $message->message;
                $data['created_at'] = $message->created_at;
                $data['message_id'] = $message->id;

                event(new PrivateMessageEvent($data));

                return response()->json([
                    'data' => $data,
                    'success' => true,
                    'message' => 'message sent successfully'
                ]);
            } 
            catch(\Exception $error) {
                $message->delete();
            }
        }
    }

    public function sendGroupMessage(Request $request) {
        $request->validate([
            'message' => 'required',
            'message_group_id' => 'required'
        ]);

        $sender_id = auth()->user()->id;
        $message_group_id = $request->message_group_id;

        $message = new Message();
        $message->message = $request->message;

        if($message->save()) {
            try {
                $message->users()->attach($sender_id, [ 'message_group_id' => $message_group_id]);
                $sender = User::where('id', '!=', $sender_id)->first();

                $data = [];
                $data['sender_id'] = $sender_id;
                $data['sender_name'] = $sender->name;
                $data['group_id'] = $message_group_id;
                $data['content'] = $message->message;
                $data['created_at'] = $message->created_at;
                $data['message_id'] = $message->id;
                $data['type'] = 2;

                event(new GroupMessageEvent($data));

                return response()->json([
                    'data' => $data,
                    'success' => true,
                    'message' => 'message sent successfully'
                ]);
            } 
            catch(\Exception $error) {
                $message->delete();
            }
        }
    }
}
