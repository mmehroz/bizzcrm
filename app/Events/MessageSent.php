<?php

namespace App\Events;

use App\Models\User;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use DB;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * User that sent the message
     *
     * @var \App\User
     */
    public $user;

    /**
     * Message details
     *
     * @var \App\Message
     */
    public $message = [];

    /**
     * Members details
     *
     * @var \App\Message
     */
    public $members = [];


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $message)
    {
        $this->user = $user;
        $this->message = $message;
        // $this->members[] = $members;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        if(isset($this->message['message']['message_to'])){
            return new PrivateChannel('bwccrm-chat'.$this->message['message']['message_to']);
        }else{
            $groupuser = DB::table('groupmember')->where('group_id', $this->message['message']['group_id'])->select('groupmember.user_id')->get();
            $splituser = array();
            foreach ($groupuser as $groupusers) {
                $splituser[] = 'bwccrm-chat'.$groupusers->user_id;
            }
            // dd($splituser);
            return ($splituser);
            // $groupchannelname = array();
            // foreach ($splituser as $splitusers) {
            // $groupchannelname[] = 'bwccrm-chat'.$splitusers;
            // }
        }
    }

    public function broadcastAs()
    {
        return 'messaging';
    }

    public function broadcastWith()
    {
        return [
            'user' => $this->user,     
            'message' => $this->message,
  
        ];
    }
}
