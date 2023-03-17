<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\RemoveGroupMember;
use App\Events\AddGroupMember;
use App\Models\User;
use App\Models\Message;
use App\Models\Group;
use App\Models\GroupMessage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class chatController extends Controller
{
	public $allowed_images = array('png','jpg','jpeg','gif','bmp','PNG','JPG','JPEG','GIF','BMP');
    public $allowed_files  = array('zip','rar','txt','pdf','ai','eps','cdr','psd','dst','pes','ofm','pxf',);
	public function getAllowedImages(){
        return $this->allowed_images;
    }
    public function getAllowedFiles(){
        return $this->allowed_files;
    }
    public $successStatus = 200;
    public function sendMessage(Request $request)
    {
        $user = DB::table('user')->where('user_id', $request->loginuser_id)->first();
        $error_msg =  $attachment_type = $attachmentorname = $attachmentnewname = null;
        $att_new_name = array();
        $original_name = array();
        $message_id = $msg_get = $user_from = null;
        $index =0;
        $groupmessage = $message = $messageData = $members = array();
        if ($request->hasFile('message_attachment')) {
            $fil = $request->file('message_attachment');
            $indexattachment=0;
        	foreach($fil as $file){
            if ($file->getSize() < 150000000) {
                            $original_name[$indexattachment] = $file->getClientOriginalName();
                            $att_new_name[$indexattachment] = Str::uuid() . "." . $file->getClientOriginalExtension();
                            Storage::putFileAs('public\\chat_attachments\\', $file, $att_new_name[$indexattachment]);
                            // dd($original_name);

            } else {
                $error_msg = "File size is too long!";
            }
            $indexattachment++;
        	}
                $attachmentorname = implode(',', $original_name);
                $attachmentnewname = implode(',', $att_new_name);
        }
        if (!$error_msg) {
            if(!$request['group_id']){
                $message = array(
                    'message_from' => $user->user_id,
                    'message_to' => $request['message_to'],
                    'message_body' => $request['message_body'],
                    'message_attachment' => ($attachmentnewname) ? $attachmentnewname : null,
                    'message_originalname' => ($attachmentorname) ? $attachmentorname : null,
                    'status_id' => 1,
                    'message_quoteid' => $request['message_quoteid'],
                    'message_quotebody' => $request['message_quotebody'],
                    'message_quoteuser' => $request['message_quoteuser'],
                );
                $message_created = Message::create($message);
        // dd($message);
                $message_id = DB::getPdo()->lastInsertId();
                $msg_get = Message::where('message_id', $message_id)->first();
                $user_from = DB::table('user')->where('user_id', $msg_get->message_from)->first();
                $messageData = array(
                    'message_id' => $msg_get->message_id,
                    'message_from' => $msg_get->message_from,
                    'message_to' => $msg_get->message_to,
                    'message_body' => $msg_get->message_body,
                    'message_attachment' => $msg_get->message_attachment,
                    'message_originalname' => $msg_get->message_originalname,
                    'message_seen' => $msg_get->message_seen,
                );
                $sendingtoname = DB::table('user')->where('user_id', $msg_get->message_from)->select('user_name')->first();
                $ch = curl_init("https://8562583c-fb65-44fd-8627-417d33f86cb0.pushnotifications.pusher.com/publish_api/v1/instances/8562583c-fb65-44fd-8627-417d33f86cb0/publishes");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "Authorization:Bearer 252BAE737B14B855E19A86C8F352A07206F940D6DF731BF6ACC168F81E36CDC8" ));
                curl_setopt($ch, CURLOPT_POSTFIELDS, '{"interests":["hello"],"web":{"notification":{"title":"'.$sendingtoname->user_name.'","body":"'.$msg_get->message_body.'"}}}');
                $result = curl_exec($ch);
                // dd($result);
                // curl_setopt($ch,CURLOPT_POSTFIELDS, ['data' => $postfields]);
            }
            else{
                $groupmessage = array(
                    'user_id' => $user->user_id,
                    'group_id' => $request['group_id'],
                    'groupmessage_body' => $request['message_body'],
                    'groupmessage_attachment' => ($attachmentnewname) ? $attachmentnewname : null,
                    'groupmessage_originalname' => ($attachmentorname) ? $attachmentorname : null,
                    'status_id' => 1,
                    'groupmessage_quoteid' => $request['message_quoteid'],
                    'groupmessage_quotebody' => $request['message_quotebody'],
                    'groupmessage_quoteuser' => $request['message_quoteuser'],
                );
                $groupmessage_created = GroupMessage::create($groupmessage);
                $groupmessage_id = DB::getPdo()->lastInsertId();
                $msg_get = GroupMessage::where('groupmessage_id', $groupmessage_id)->first();
                $user_from = DB::table('user')->where('user_id', $msg_get->user_id)->first();
                $messageData = array(
                    'message_id' => $msg_get->groupmessage_id,
                    'message_from' => $msg_get->user_id, //'user_id', => $msg_get->user_id,
                    'group_id' => $msg_get->group_id,
                    'message_body' => $msg_get->groupmessage_body,
                    'message_attachment' => $msg_get->groupmessage_attachment,
                    'message_originalname' => $msg_get->groupmessage_originalname,
                );
                }
                $messageData['message_from_name'] = $user_from->user_name;
                $messageData['user_picture'] = $user_from->user_picture;
                $messageData['message_time'] = $msg_get->created_at->diffForHumans(Carbon::now());
                $messageData['message_fullTime'] = $msg_get->created_at->toDateTimeString();
                Event::dispatch(new MessageSent(
                $user,
                [
                    'user' => $user->user_id,
                    'message' => $messageData,
                    // 'members' => $members,
                ],
                ));
                if($request['group_id']){
                $groupmembers = DB::table('groupmember')->where('group_id', $request['group_id'])->get();
                foreach($groupmembers as $single_member){
                    $members[$index] = $single_member->user_id;
                    $index++;
                }
                Event::dispatch(new MessageSent(
                    $user,
                    [
                        'user' => $user->user_id,
                        'message' => $messageData,
                    ],
                    $members,
                ));
            }
        }
        // event(new MessageSent('hello world','1'));
        // broadcast(new MessageSent('hello world','1'))->toOthers();
        // dd($groupmessage_created);
        return response()->json(['data' => $messageData,'members' => $members,'message' => 'Message Sent Successfully'],200);
    }
    public function fetchMessage(Request $request)
    {
        $attachment = $attachment_type = $attachment_title = null;
        $allMessages = array();
        $messages = Message::where('message_from', $request['from_id'])->where('message_to', $request['to_id'])
                    ->orWhere('message_from', $request['to_id'])->where('message_to', $request['from_id'])->get();
      if ($messages->count() > 0) {
            foreach ($messages as $single_message) {
            $msg = Message::where('message_id', $single_message->message_id)->first();
                if($msg->message_attachment){
                    $ext = pathinfo($msg->message_attachment, PATHINFO_EXTENSION);
                    $attachment_type = in_array($ext, $this->getAllowedImages()) ? 'image' : 'file';
                }
                $message_user = DB::table('user')->where('user_id', $msg->message_from)->first();
                $nowMessage = array(
                    'message_id' => $msg->message_id,
                    'message_from' => $msg->message_from,
                    'from_username' => $message_user->user_name,
                    'from_userpicture' => $message_user->user_picture,
                    'message_to' => $msg->message_to,
                    'message_body' => $msg->message_body,
                    'message_attachment' => $msg->message_attachment,
                    'message_originalname' => $msg->message_originalname,
                    'attachment_type' => $attachment_type,
                    'time' => $msg->created_at->diffForHumans(Carbon::now()),
                    'fullTime' => $msg->created_at->toDateTimeString(),
                    'seen' => $msg->message_seen,
                );
                array_push($allMessages, $nowMessage);
            }
            return Response::json([
                'count' => $messages->count(),
                'messages' => $allMessages,//$messages,
            ]);
        }
        else{
            return Response::json([
                'count' => 0,
                'messages' =>  array(),
            ], $this->successStatus);
        }
    }
    public function fetchMessageGroup(Request $request)
    {
        $attachment = $attachment_type = $attachment_title = null;
        $allMessages = array();
        $groupmessages = GroupMessage::where('group_id', $request['group_id'])->get();//->where('from_id',Auth::guard('api')->user()->id);//->get();
        if ($groupmessages->count() > 0) {
            foreach ($groupmessages as $single_message) {
                $msg = GroupMessage::where('groupmessage_id', $single_message->groupmessage_id)->first();
                if($msg->groupmessage_attachment){
                    $ext = pathinfo($msg->groupmessage_attachment, PATHINFO_EXTENSION);
                    $attachment_type = in_array($ext, $this->getAllowedImages()) ? 'image' : 'file';
                }
                $groupmessage_user = DB::table('user')->where('user_id', $msg->user_id)->first();
                $nowMessage = array(
                    'message_id' => $msg->groupmessage_id,
                    'from_userid' => $msg->user_id,
                    'from_username' => $groupmessage_user->user_name,
                    'from_userpicture' => $groupmessage_user->user_picture,
                    'group_id' => $msg->group_id,
                    'groupmessage_body' => $msg->groupmessage_body,
                    'groupmessage_attachment' => $msg->groupmessage_attachment,
                    'groupmessage_originalname' => $msg->groupmessage_originalname,
                    'attachment_type' => $attachment_type,
                    'time' => $msg->created_at->diffForHumans(Carbon::now()),
                    'fullTime' => $msg->created_at->toDateTimeString(),
                    'seen' => $msg->message_seen,
                );
                array_push($allMessages, $nowMessage);
            }
            return Response::json([
                'count' => $groupmessages->count(),
                'messages' => $allMessages,//$groupmessages,
            ]);
        }
        else{
            return Response::json([
                'count' => 0,
                'messages' =>  array(),
            ], $this->successStatus);
        }
    }
    public function getContactsUser(Request $request)
    {
    	$loginuser_id =  $request->loginuser_id;
        $users = Message::join('user',  function ($join)use($loginuser_id) {
            $join->on('message.message_from', '=', 'user.user_id')
                ->orOn('message.message_to', '=', 'user.user_id');
        })
            ->where('message.message_from', $loginuser_id)
            ->orWhere('message.message_to', $loginuser_id)
            ->orderBy('message.created_at', 'desc')
            ->get()
            ->unique('user_id');
            if ($users->count() > 0) {
            $contacts = null;
            $contacts = $userCollection = [] ;
            foreach ($users as $singleuser) {
                if ($singleuser->user_id != $loginuser_id) {
                    $userCollection = DB::table('user')->where('user_id', $singleuser->user_id)->first();
                    $unseen = Message::where('message_from',$singleuser->user_id)->where('message_to', $loginuser_id)
                    ->where('message_seen', 0)->count();
                    $userCollection->unseen = $unseen;
                    $userCollection->last_msg = Message::where('message_from',$loginuser_id)->where('message_to', $singleuser->user_id)
        			->orWhere('message_from', $singleuser->user_id)->where('message_to',$loginuser_id)
        			->orderBy('created_at','DESC')->latest()->first();
                    array_push($contacts, $userCollection);
                }
            }
        }
		return response()->json([
            'contacts' => $users->count() > 0 ? $contacts : [],/*'Your contact list is empty',*/
        ], $this->successStatus);
    }

    public function getContactsTotal(Request $request)
    {
        $campaign_id = $request->campaign_id;
        $contacts_total = DB::table('user')->where('campaign_id', $campaign_id)->where('status_id', 1)->select('user_id', 'user_name', 'role_id', 'user_picture')->get();
        return response()->json([
            'contacts' => $contacts_total,/*'Your contact list is empty',*/
        ], $this->successStatus);
    }
    public function searchUser(Request $request)
    {
        if (empty($request->input)) {
            $arrayempty = array();
             return response()->json(['records' => $arrayempty], $this->successStatus);
        }
        $getRecords = null;
        $input = trim(filter_var($request->input, FILTER_SANITIZE_STRING));
        $records = DB::table('user')->where(function($query)use($input){
            $query->orWhere('user_name', 'LIKE', "%{$input}%");
            $query->orWhere('user_email', 'LIKE', "%{$input}%");
        })
        ->where('user_name', '!=',  $request->loginuser_name)
        ->get();
        return response()->json([
            'records' => $records->count() > 0
                ? $records
                : [],
        ], $this->successStatus);
    }
    public function download(Request $request)
    {
    	// return response()->json("ssss");
    	$fileName = $request->fileName;
        $path = storage_path() . '/app/public/chat_attachments/'. $fileName;
        if (file_exists($path)) {

            $att_get = Message::where('message_attachment', $fileName)->first();
            $original_name = $att_get['message_originalname'];
            return Storage::disk('chat')->download($fileName, $original_name);
        } else {
            return abort(404, "Sorry, File does not exist in our server or may have been deleted!");
        }
    }
    public function makeSeen(Request $request){
        $seen = Message::Where('message_from',$request->user_id)
                ->where('message_to',$request->loginuser_id)
                ->where('message_seen', 0)
                ->update(['message_seen' => 1]);
        return Response::json([
            'status' => $seen,
        ], $this->successStatus);
    }
    public function unseen(Request $request)
    {
    	$unseen = Message::where('message_from',$request->user_id)->where('message_to', $request->loginuser_id)
                    ->where('message_seen', 0)->count();
        return response()->json([
            'num_unseen' => $unseen,
        ], $this->successStatus);
    }
   public function getAllGroups(Request $request)
    {
        $groups = Group::where('status_id', 1)
        ->get();
        return $groups;
    }
    public function getUserGroups(Request $request)
    {
        $usergroups = DB::table('groupmember')->where('user_id', $request->loginuser_id)->get();
        $groups = [];
        foreach($usergroups as $singlegroup){
        $groupget = DB::table('group')->where('group.group_id', $singlegroup->group_id)
        	->where('group.status_id', 1)
            ->first();
            if($groupget != null){
            array_push($groups, $groupget);
            }
        }
        $index=0;
        foreach ($groups as $groupss) {
            $groupmessage = DB::table('groupmessage')->where('groupmessage.group_id', $groupss->group_id)
            ->orderBy('groupmessage.created_at', 'desc')
            ->select('groupmessage.groupmessage_body','groupmessage.groupmessage_attachment','groupmessage.created_at')
            ->first();
            if (isset($groupmessage->groupmessage_body)) {
            $groups[$index]->lastmessage = $groupmessage->groupmessage_body;
            $groups[$index]->attachment = $groupmessage->groupmessage_attachment;
            $groups[$index]->groupmessagetime = $groupmessage->created_at;
            }else{
            $groups[$index]->lastmessage = "";
            $groups[$index]->attachment = "";
            $groups[$index]->groupmessagetime = "";
            }
            $index++;
        }
            
        return $groups;
    }
    public function createGroup(Request $request)
    {
        $form_data = array(
            'group_name'  	=>  $request->group_name,
            'group_image' 	=> 	NULL,
            'created_by' 	=> 	$request->loginuser_id,
            'status_id' 	=>  1,
        );
        if($request->group_image != ''){
        	$image_string = $request->group_image;
        	$extension = $image_string->getClientOriginalExtension();
            $img_rand = rand(1,999).date('Y-m-d');
            $new_logo = $img_rand.'.'.$extension;
            Storage::putFileAs('public\\chat_attachments\\', $image_string, $new_logo);
            $form_data['group_image'] = $new_logo;
        }
            $members = $request->members;
            $group = new Group();
            $group = Group::create($form_data);
            $group_id = DB::getPdo()->lastInsertId();
            foreach ($members as $memberss) {
			$adds[] = array(
				'group_id' 		=> $group_id,
				'user_id' 		=> $memberss,
				'status_id'		=> 1,
				'created_at'	=> date('Y-m-d h:i:s'),
				);
			}
			DB::table('groupmember')->insert($adds);
            $groupcreated = Group::where('group_id', $group_id)
            ->select('group_id', 'group_name', 'group_image', 'created_by', 'status_id', 'created_at')->get();
        return response()->json(["success" => true, "group" => $groupcreated, "message" => "Group created successfully"], $this->successStatus);
    }
    public function updateGroup(Request $request)
    {
    	$group_id = $request->group_id;
        $group =  $this->getGroup($group_id);
        $form_data = array(
            'group_name' => $request->group_name ? $request->group_name : $group->group_name,
            'created_by' => $request->loginuser_id ? $request->loginuser_id : $group->created_by,
            'status_id' => 1 ? 1 : $group->status,
        );
        if($request->file('group_image'))
        {
            $image = $request->file('group_image');
            $group_image = Str::random(15) . '.' . $image->getClientOriginalExtension();
            $image->move(storage_path('/public/chat_attachments/'), $group_image);
            $form_data['group_image'] = $group_image;
        }
        $group = Group::where('group_id', $group_id)->update($form_data);
        $groupattach = Group::where('group_id', $group_id)->first(); //continue from
        $groupupdated = Group::where('group_id', $group_id)
            ->select('group_id', 'group_name', 'group_image', 'created_by', 'status_id', 'created_at')->first();
        return response()->json(["success" => true, "groupupdated" => $groupupdated, "message" => "Group Updated Successfully"], $this->successStatus);
    }
    public function archiveGroup(Request $request)
    {
    	$group_id = $request->group_id;
        $group = $this->getGroup($group_id);
        $archive_data = array(
            'status_id'  =>   2,
        );
        $group = Group::where('group_id', $group_id)->update($archive_data);
        $archivegroupmember  = DB::table('groupmember')
			->where('group_id','=',$request->group_id)
			->update([
			'status_id' 		=> 2,
			]); 
        return response()->json(["message" => "Group Archived Successfully"], $this->successStatus);
    }
	// Functions Use In Controller Start
    public function getGroup($group_id)
    {
    	$group_id  = $group_id;
        $group = DB::table('group')
		->where('status_id','=',1)
		->where('group_id','=',$group_id)
		->select('group.*')
		->first();
		$groupmember = DB::table('group')
		->join('groupmember','groupmember.group_id', '=','group.group_id')
		->where('group.status_id','=',1)
		->where('group.group_id','=',$group_id)
		->select('groupmember.user_id')
		->get();
		$getmembersuserid = array();
		foreach ($groupmember as $groupmembers) {
			$getmembersuserid[]  = $groupmembers->user_id;
		}
		$alldata  =  array('group' => $group,'members' => $getmembersuserid);
		return $alldata;
	}
    public function addmember(Request $request)
    {
        $user = $request->group_id;
        $adds[] = array(
            'group_id'      => $request->group_id,
            'user_id'       => $request->member_id,
            'status_id'     => 1,
            'created_at'    => date('Y-m-d h:i:s'),
            );
        DB::table('groupmember')->insert($adds);
        Event::dispatch(new AddGroupMember(
        $user,
        [
            'group' => $user,
            'member' => $request->member_id,
            // 'members' => $members,
        ],
        ));
        return response()->json(["success" => true, "message" => "Member Added Successfully"], $this->successStatus);
    }
    public function removemember(Request $request)
    {
        $user = $request->group_id;
        DB::connection('mysql')->table('groupmember')
        ->where('user_id','=',$request->member_id)
        ->update([
       'status_id' => 2,
        ]);
        Event::dispatch(new RemoveGroupMember(
        $user,
        [
            'group' => $user,
            'member' => $request->member_id,
            // 'members' => $members,
        ],
        ));
        return response()->json(["success" => true, "message" => "Member Remove Successfully"], $this->successStatus);
    }
    // Functions Use In Controller End
}