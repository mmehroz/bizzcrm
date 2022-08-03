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

class RemoveGroupMember implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * User that sent the message
     *
     * @var \App\User
     */
    public $group;

    /**
     * Message details
     *
     * @var \App\Message
     */
    public $member;

    /**
     * Members details
     *
     * @var \App\Message
     */
    // public $members = [];


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($group, $member)
    {
        $this->group = $group;
        $this->member = $member;
        // $this->members[] = $members;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
            $groupuser = DB::table('groupmember')->where('group_id', $this->group)->where('status_id', 1)->select('groupmember.user_id')->get();
            $splituser = array();
            foreach ($groupuser as $groupusers) {
                $splituser[] = 'bwccrm-chat'.$groupusers->user_id;
            }
            return ($splituser);
    }

    public function broadcastAs()
    {
        return 'removing';
    }

    public function broadcastWith()
    {
        return [
            'group' => $this->group,     
            'member' => $this->member,
  
        ];
    }
}
