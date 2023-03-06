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
use Validator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class testchatController extends Controller
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
    // dd($request);
        $user = DB::table('elsemployees')->where('elsemployees_empid', $request->loginuser_id)->first();
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
                return response()->json(['message' => 'File size is too large!'],400);
                // $error_msg = "File size is too long!";
            }
            $indexattachment++;
        	}
                $attachmentorname = implode(',', $original_name);
                $attachmentnewname = implode(',', $att_new_name);
        }
        if (!$error_msg) {
            if(!$request['group_id']){
                $message = array(
                    'message_from' => $user->elsemployees_empid,
                    'message_to' => $request['message_to'],
                    'message_body' => $request['message_body'],
                    'message_attachment' => ($attachmentnewname) ? $attachmentnewname : $request->message_attachment,
                    'message_originalname' => ($attachmentorname) ? $attachmentorname : $request->message_originalname,
                    'status_id' => 1,
                    'message_quoteid' => $request['message_quoteid'],
                    'message_quotebody' => $request['message_quotebody'],
                    'message_quoteuser' => $request['message_quoteuser'],
                    'message_forwarded' => $request['message_forwarded'],
                );
                $message_created = Message::create($message);
        // dd($message_created);
                $message_id = DB::getPdo()->lastInsertId();
                $msg_get = Message::where('message_id', $message_id)->first();
                $user_from = DB::table('elsemployees')->where('elsemployees_empid', $msg_get->message_from)->first();
                $messageData = array(
                    'message_id' => $msg_get->message_id,
                    'message_from' => $msg_get->message_from,
                    'message_to' => $msg_get->message_to,
                    'message_body' => $msg_get->message_body,
                    'message_attachment' => $msg_get->message_attachment,
                    'message_originalname' => $msg_get->message_originalname,
                    'message_seen' => $msg_get->message_seen,
                    'message_quoteid' => $msg_get->message_quoteid,
                    'message_quotebody' => $msg_get->message_quotebody,
                    'message_quoteuser' => $msg_get->message_quoteuser,
                    'message_forwarded' => $msg_get->message_forwarded,
                );
                    // $sendingtoname = DB::table('elsemployees')->where('elsemployees_empid', $msg_get->message_from)->select('elsemployees_name')->first();
                    // $ch = curl_init("https://8562583c-fb65-44fd-8627-417d33f86cb0.pushnotifications.pusher.com/publish_api/v1/instances/8562583c-fb65-44fd-8627-417d33f86cb0/publishes");
                    // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "Authorization:Bearer 252BAE737B14B855E19A86C8F352A07206F940D6DF731BF6ACC168F81E36CDC8" ));
                    // curl_setopt($ch, CURLOPT_POSTFIELDS, '{"interests":["'.$msg_get->message_to.'"],"web":{"notification":{"title":"'.$sendingtoname->elsemployees_name.'","body":"'.$msg_get->message_body.'"}}}');
                    // $result = curl_exec($ch);
            }
            else{
                $groupmessage = array(
                    'user_id' => $user->elsemployees_empid,
                    'group_id' => $request['group_id'],
                    'groupmessage_body' => $request['message_body'],
                    'groupmessage_attachment' => ($attachmentnewname) ? $attachmentnewname : $request->groupmessage_attachment,
                    'groupmessage_originalname' => ($attachmentorname) ? $attachmentorname : $request->groupmessage_originalname,
                    'status_id' => 1,
                    'groupmessage_quoteid' => $request['message_quoteid'],
                    'groupmessage_quotebody' => $request['message_quotebody'],
                    'groupmessage_quoteuser' => $request['message_quoteuser'],
                    'groupmessage_forwarded' => $request['groupmessage_forwarded'],
                );
                $groupmessage_created = GroupMessage::create($groupmessage);
                $groupmessage_id = DB::getPdo()->lastInsertId();
                $msg_get = GroupMessage::where('groupmessage_id', $groupmessage_id)->first();
                $user_from = DB::table('elsemployees')->where('elsemployees_empid', $msg_get->user_id)->first();
                $messageData = array(
                    'message_id' => $msg_get->groupmessage_id,
                    'from_userid' => $msg_get->user_id, //'user_id', => $msg_get->user_id,
                    'group_id' => $msg_get->group_id,
                    'groupmessage_body' => $msg_get->groupmessage_body,
                    'groupmessage_attachment' => $msg_get->groupmessage_attachment,
                    'groupmessage_originalname' => $msg_get->groupmessage_originalname,
                    'groupmessage_quoteid' => $msg_get->groupmessage_quoteid,
                    'groupmessage_quotebody' => $msg_get->groupmessage_quotebody,
                    'groupmessage_quoteuser' => $msg_get->groupmessage_quoteuser,
                    'groupmessage_forwarded' => $msg_get->groupmessage_forwarded,
                );
                $getgroupmember = DB::table('groupmember')
                ->select('user_id')
                ->where('status_id','=',1)
                ->where('group_id','=',$request['group_id'])
                ->get();
                foreach ($getgroupmember as $getgroupmembers) {
                 $addseen[] = array(
                'groupmessageseen_seen'     => 1,
                'group_id'                  => $msg_get->group_id,
                'groupmessage_id'           => $msg_get->groupmessage_id,
                'user_id'                   => $getgroupmembers->user_id,
                'status_id'                 => 1,
                'created_at'                => date('Y-m-d h:i:s')
                );
                }
                DB::connection('mysql')->table('groupmessageseen')->insert($addseen);
                }
                $messageData['from_username'] = $user_from->elsemployees_name;
                $messageData['from_userpicture'] = $user_from->elsemployees_image;
                $messageData['message_time'] = $msg_get->created_at->diffForHumans(Carbon::now());
                $messageData['fullTime'] = $msg_get->created_at->toDateTimeString();
                // Event::dispatch(new MessageSent(
                // $user,
                // [
                //     'user' => $user->elsemployees_empid,
                //     'message' => $messageData,
                // ],
                // ));
                if($request['group_id']){
                $groupmembers = DB::table('groupmember')->where('group_id', $request['group_id'])->get();
                foreach($groupmembers as $single_member){
                    $members[$index] = "$single_member->user_id";
                    $index++;
                }
                //  $sendingtogroupname = DB::table('group')->where('group_id', $msg_get->group_id)->select('group_name')->first();
                //     $ch = curl_init("https://8562583c-fb65-44fd-8627-417d33f86cb0.pushnotifications.pusher.com/publish_api/v1/instances/8562583c-fb65-44fd-8627-417d33f86cb0/publishes");
                //     curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "Authorization:Bearer 252BAE737B14B855E19A86C8F352A07206F940D6DF731BF6ACC168F81E36CDC8" ));
                //     curl_setopt($ch, CURLOPT_POSTFIELDS, '{"interests":'.json_encode($members).',"web":{"notification":{"title":"'.$user_from->elsemployees_name.' in '.$sendingtogroupname->group_name.'","body":"'.$msg_get->groupmessage_body.'"}}}');
                //     $result = curl_exec($ch);
                // Event::dispatch(new MessageSent(
                //     $user,
                //     [
                //         'user' => $user->elsemployees_empid,
                //         'message' => $messageData,
                //     ],
                //     $members,
                // ));
            }
        }
        // dd($message_created);
        // event(new MessageSent('hello world','1'));
        // broadcast(new MessageSent('hello world','1'))->toOthers();
        return response()->json(['data' => $messageData,'members' => $members,'message' => 'Message Sent Successfully'],200);
    }
    public function fetchMessage(Request $request)
    {
    	$messages = DB::table('fetchmessage')
        ->select('*')
        ->where('status_id','=',1)
        ->where('message_from','=',$request['from_id'])
        ->where('message_to','=',$request['to_id'])
        ->orWhere('message_from','=',$request['to_id'])
        ->where('status_id','=',1)
        ->where('message_to','=',$request['from_id'])
        ->orderBy('fullTime','DESC')
        ->limit(50)
        ->get()->toArray(); 	
    	if ($messages) {
            return Response::json([ 'messages' => $messages]);
        }
        else{
            return Response::json(['count' => 0,'messages' =>  array(), $this->successStatus]);
        }
    }
    public function fetchMoreMessage(Request $request)
    {
    	$messages = DB::table('fetchmessage')
        ->select('*')
        ->where('status_id','=',1)
        ->where('message_id','<',$request->message_id)
        ->where('message_from','=',$request['from_id'])
        ->where('message_to','=',$request['to_id'])
        ->orWhere('message_from','=',$request['to_id'])
        ->where('message_to','=',$request['from_id'])
        ->where('message_id','<',$request->message_id)
        ->orderBy('fullTime','DESC')
        ->limit(20)
        ->get()->toArray(); 
    	if ($messages) {
            return Response::json([ 'messages' => $messages]);
        }
        else{
            return Response::json(['count' => 0,'messages' =>  array(), $this->successStatus]);
        }
    }
   	public function fetchMessageGroup(Request $request)
   	{
   		$messages = DB::table('fetchgroupmessage')
        ->select('*')
        ->where('status_id','=',1)
        ->where('group_id', $request['group_id'])
        ->orderBy('fullTime','DESC')
        ->limit(50)
        ->get()->toArray(); 
   		$getgroupmember = DB::table('groupmember')
        ->select('user_id')
        ->where('status_id','=',1)
        ->where('group_id','=',$request['group_id'])
        ->get();
        $getseenmsgid = array();
        $seenindex = 0;
        foreach ($getgroupmember as $getgroupmembers) {
        	$getseenmsgid[$seenindex] = DB::table('groupmessageseen')
        	->join('elsemployees','elsemployees.elsemployees_empid', '=','groupmessageseen.user_id')
	        ->select('groupmessage_id','user_id','elsemployees_image','elsemployees_name')
	        ->where('group_id','=', $request['group_id'])
	        ->where('user_id','=',$getgroupmembers->user_id)
	        ->where('groupmessageseen_seen','=', 2)
	        ->orderBy('groupmessageseen_id','DESC')
	        ->first();
	        $seenindex++;
        }
        $seendata = array();
		$seendataindex = 0;
        foreach ($getseenmsgid as $getseenmsgids) {
        if (isset($getseenmsgids->groupmessage_id)) {
        	$seendata[$seendataindex]['messageid'] 		= $getseenmsgids->groupmessage_id;
        	$seendata[$seendataindex]['userid'] 		= $getseenmsgids->user_id;
        	$seendata[$seendataindex]['userpicture'] 	= $getseenmsgids->elsemployees_image;
            $seendata[$seendataindex]['username']       = $getseenmsgids->elsemployees_name;
        	$seendataindex++;
        }else{
        	$seendata[$seendataindex]['messageid'] 		= "";	
			$seendata[$seendataindex]['userid'] 		= "";	
			$seendata[$seendataindex]['userpicture'] 	= "";
            $seendata[$seendataindex]['username']       = "";
        }
        }
        if ($messages) {
            return Response::json([ 'messages' => $messages, 'seendata' => $seendata]);
        }
        else{
            return Response::json(['count' => 0,'messages' =>  array(), $this->successStatus]);
        }
    }
    public function fetchMoreMessageGroup(Request $request)
   	{
   		$messages = DB::table('fetchgroupmessage')
        ->select('*')
        ->where('groupmessage_id','<',$request->groupmessage_id)
        ->where('group_id', $request['group_id'])
        ->orderBy('fullTime','DESC')
        ->limit(20)
        ->get()->toArray(); 
        $getgroupmember = DB::table('groupmember')
        ->select('user_id')
        ->where('status_id','=',1)
        ->where('group_id','=',$request['group_id'])
        ->get();
        $getseenmsgid = array();
        $seenindex = 0;
        foreach ($getgroupmember as $getgroupmembers) {
            $getseenmsgid[$seenindex] = DB::table('groupmessageseen')
            ->join('elsemployees','elsemployees.elsemployees_empid', '=','groupmessageseen.user_id')
            ->select('groupmessage_id','user_id','elsemployees_image','elsemployees_name')
            ->where('group_id','=', $request['group_id'])
            ->where('user_id','=',$getgroupmembers->user_id)
            ->where('groupmessageseen_seen','=', 2)
            ->orderBy('groupmessageseen_id','DESC')
            ->first();
            $seenindex++;
        }
        $seendata = array();
        $seendataindex = 0;
        foreach ($getseenmsgid as $getseenmsgids) {
        if (isset($getseenmsgids->groupmessage_id)) {
            $seendata[$seendataindex]['messageid']      = $getseenmsgids->groupmessage_id;
            $seendata[$seendataindex]['userid']         = $getseenmsgids->user_id;
            $seendata[$seendataindex]['userpicture']    = $getseenmsgids->elsemployees_image;
            $seendata[$seendataindex]['username']       = $getseenmsgids->elsemployees_name;
            $seendataindex++;
        }else{
            $seendata[$seendataindex]['messageid']      = "";   
            $seendata[$seendataindex]['userid']         = "";   
            $seendata[$seendataindex]['userpicture']    = "";
            $seendata[$seendataindex]['username']       = "";
        }
        }
   		if ($messages) {
            return Response::json([ 'messages' => $messages, 'seendata' => $seendata]);
        }
        else{
            return Response::json(['count' => 0,'messages' =>  array(), $this->successStatus]);
        }
    }
 	public function testgetContactsUser(Request $request)
    {
        $loginuser_id =  $request->loginuser_id;
        // $users = Message::join('elsemployees',  function ($join)use($loginuser_id) {
        //     $join->on('message.message_from', '=', 'elsemployees.elsemployees_empid')
        //         ->orOn('message.message_to', '=', 'elsemployees.elsemployees_empid');
        // })
        //     ->where('message.message_from', $loginuser_id)
        //     ->orWhere('message.message_to', $loginuser_id)
        //     ->orderBy('message.created_at', 'desc')
        //     ->get()
        //     ->unique('elsemployees_empid');
            $users = DB::table('getchatuser')
            ->where('message_from', $loginuser_id)
            ->orWhere('message_to', $loginuser_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('elsemployees_empid');
            if ($users->count() > 0) {
            $contacts = null;
            $contacts = $userCollection = [] ;
            foreach ($users as $singleuser) {
                if ($singleuser->elsemployees_empid != $loginuser_id) {
                    $userCollection = DB::table('elsemployees')->where('elsemployees_empid', $singleuser->elsemployees_empid)->select('elsemployees_empid','elsemployees_name','elsemployees_image')->first();
                    $unseen = Message::where('message_from',$singleuser->elsemployees_empid)->where('message_to', $loginuser_id)
                    ->where('message_seen', 0)->count();
                    $userCollection->unseen = $unseen;
                    $userCollection->last_msg = Message::where('message_from',$loginuser_id)
                    ->where('message_to', $singleuser->elsemployees_empid)
                    ->where('status_id', 1)
                    ->orWhere('message_from', $singleuser->elsemployees_empid)->where('message_to',$loginuser_id)
                    ->orderBy('created_at','DESC')->latest()->first();
                    array_push($contacts, $userCollection);
                }
            }
        }
        $contacts = $this->paginate($contacts);
        return response()->json([
            'contacts' => $users->count() > 0 ? $contacts : [],/*'Your contact list is empty',*/
        ], $this->successStatus);
    }
    public function getContactsTotal(Request $request)
    {
        $campaign_id = $request->campaign_id;
        $contacts_total = DB::table('elsemployees')->where('elsemployees_status', 2)->select('elsemployees_empid', 'elsemployees_name', 'elsemployees_roleid', 'elsemployees_image')->get();
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
        $records = DB::table('elsemployees')->where(function($query)use($input){
            $query->orWhere('elsemployees_name', 'LIKE', "%{$input}%");
            $query->orWhere('elsemployees_email', 'LIKE', "%{$input}%");
        })
        ->where('elsemployees_name', '!=',  $request->loginuser_name)
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
        // dd($path);
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
    public function testgetUserGroups(Request $request)
    {
        $usergroups = DB::table('groupmember')->where('user_id', $request->loginuser_id)->where('status_id', 1)->get();
        $groups = [];
        $indexmember=0;
        foreach($usergroups as $singlegroup){
        $groupget = DB::table('group')->where('group.group_id', $singlegroup->group_id)
        	->where('group.status_id', 1)
            ->first();
        $getgroupmember = DB::table('groupmember')->where('group_id', $singlegroup->group_id)->where('status_id', 1)->select('user_id')->get();
        $gm = array();
        foreach ($getgroupmember as $getgroupmembers) {
            $gm[] = $getgroupmembers->user_id;

        }
        $mergemembers = implode(',', $gm);
            if($groupget != null){
            array_push($groups, $groupget);
            $groups[$indexmember]->memberid = $mergemembers;
            $indexmember++;
            }
        }
        // dd($groups);
        // $index=0;
        // foreach ($groups as $groupss) {
        //     $groupmessage = DB::table('groupmessage')->where('groupmessage.group_id', $groupss->group_id)
        //     ->where('status_id', 1)
        //     ->orderBy('groupmessage.created_at', 'desc')
        //     ->select('groupmessage.groupmessage_body','groupmessage.groupmessage_attachment','groupmessage.created_at')
        //     ->first();
        //     $getunseenmesg = DB::table('groupmessageseen')
        //     ->select('groupmessageseen_id')
        //     ->where('group_id','=', $groupss->group_id)
        //     ->where('user_id','=', $request->user_id)
        //     ->where('groupmessageseen_seen','=', 1)
        //     ->count('groupmessageseen_id'); 
        //     if (isset($groupmessage->groupmessage_body)) {
        //     $groups[$index]->lastmessage = $groupmessage->groupmessage_body;
        //     $groups[$index]->attachment = $groupmessage->groupmessage_attachment;
        //     $groups[$index]->groupmessagetime = $groupmessage->created_at;
        //     $groups[$index]->groupunseenmesg = $getunseenmesg;
        //     }else{
        //     $groups[$index]->lastmessage = "";
        //     $groups[$index]->attachment = "";
        //     $groups[$index]->groupmessagetime = "";
        //     $groups[$index]->groupunseenmesg = "";
        //     }
        //     $index++;
        // }
        // $groups = $this->paginate($groups);
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
        // dd($group['group']->group_image);
        if(!empty($request->group_image[0]))
        {
            $image_string = $request->group_image[0];
            $extension = $image_string->getClientOriginalExtension();
            $img_rand = rand(1,999).date('Y-m-d');
            $new_logo = $img_rand.'.'.$extension;
            Storage::putFileAs('public\\chat_attachments\\', $image_string, $new_logo);
        }
        $form_data = array(
            'group_name' => $request->group_name ? $request->group_name : $group->group_name,
            'group_image' => $request->group_image[0] ? $new_logo : $group['group']->group_image,
            'created_by' => $request->loginuser_id ? $request->loginuser_id : $group->created_by,
            'status_id' => 1 ? 1 : $group->status,
        );
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
        // Event::dispatch(new AddGroupMember(
        // $user,
        // [
        //     'group' => $user,
        //     'member' => $request->member_id,
        //     // 'members' => $members,
        // ],
        // ));
         $updatedmembers = DB::table('groupmember')
		->where('status_id','=',1)
		->where('group_id','=',$request->group_id)
		->select('groupmember.user_id')
		->get();
        $arrmembers = array();
        foreach ($updatedmembers as $updatedmemberss) {
            $arrmembers[] = $updatedmemberss->user_id;
        }
        $implodemembers = implode(',', $arrmembers);
        return response()->json(["success" => true, "updatedmembers" => $implodemembers, "message" => "Member Added Successfully"], $this->successStatus);
    }
    public function removemember(Request $request)
    {
    	$user = $request->group_id;
        DB::connection('mysql')->table('groupmember')
                ->where('user_id','=',$request->member_id)
                ->where('group_id','=',$request->group_id)
                ->update([
               'status_id' => 2,
                ]);
       $updatedmembers = DB::table('groupmember')
		->where('status_id','=',1)
		->where('group_id','=',$request->group_id)
		->select('groupmember.user_id')
		->get();
        $arrmembers = array();
        foreach ($updatedmembers as $updatedmemberss) {
            $arrmembers[] = $updatedmemberss->user_id;
        }
        $implodemembers = implode(',', $arrmembers);
        // dd($implodemembers);
        return response()->json(["success" => true, "updatedmembers" => $implodemembers, "message" => "Member Remove Successfully"], $this->successStatus);
    }
    public function groupparticipants(Request $request)
    {
        $participants = DB::table('groupmember')
        ->join('elsemployees','elsemployees.elsemployees_empid', '=','groupmember.user_id')
        ->where('groupmember.status_id','=',1)
        ->where('groupmember.group_id','=',$request->group_id)
        ->select('elsemployees.elsemployees_empid','elsemployees.elsemployees_name','elsemployees.elsemployees_image')
        ->get();
        return response()->json(["success" => true, "participants" => $participants, "message" => "Group Participants"], $this->successStatus);
    }
     public function makegroupmessageseen(Request $request){
     	$seen = DB::connection('mysql')->table('groupmessageseen')
        ->where('status_id','=',1)
        ->where('group_id','=',$request->group_id)
        ->where('user_id','=',$request->user_id)
        ->where('groupmessageseen_seen','=',1)
        ->update([
      		 'groupmessageseen_seen' => 2,
        ]);
     	return Response::json(['status' => $seen], $this->successStatus);
    }
     public function fetchAttachments(Request $request)
    {
    	$attachment = DB::table('fetchmessage')
        ->select('message_attachment','message_originalname')
        ->where('message_from','=',$request['from_id'])
        ->where('message_to','=',$request['to_id'])
        ->where('message_attachment','!=',null)
        ->orWhere('message_from','=',$request['to_id'])
        ->where('message_to','=',$request['from_id'])
        ->where('message_attachment','!=',null)
        ->orderBy('message_id','DESC')
        ->get()->toArray(); 	
        $finalattachment = array();
        $messageindex=0;
        foreach ($attachment as $attachments) {
        	$finalattachment[$messageindex]['message_attachment'] = $attachments->message_attachment;
            $finalattachment[$messageindex]['message_originalname'] = $attachments->message_originalname;
            $messageindex++;
        }
        if ($finalattachment) {
            return Response::json([ 'attachments' => $finalattachment, $this->successStatus]);
        }
        else{
            return Response::json(['attachments' =>  array(), $this->successStatus]);
        }
    }
    public function fetchGroupAttachments(Request $request)
   	{
   		$groupattachment = DB::table('fetchgroupmessage')
        ->select('groupmessage_attachment','groupmessage_originalname')
        ->where('group_id', $request['group_id'])
        ->where('groupmessage_attachment','!=',null)
        ->orderBy('groupmessage_id','DESC')
        ->get()->toArray(); 
   		$finalgroupattachment = array();
        $groupindex=0;
        foreach ($groupattachment as $groupattachments) {
            $finalgroupattachment[$groupindex]['groupmessage_attachment'] = $groupattachments->groupmessage_attachment;
            $finalgroupattachment[$groupindex]['groupmessage_originalname'] = $groupattachments->groupmessage_originalname;
        	$groupindex++;
        }
       	if ($finalgroupattachment) {
            return Response::json([ 'messages' => $finalgroupattachment, $this->successStatus]);
        }
        else{
            return Response::json(['messages' =>  array(), $this->successStatus]);
        }
    }
    public function getSignupUsers(Request $request)
    {
        $contacts_total = DB::table('elsemployees')->where('elsemployees_status', 3)->select('elsemployees_empid', 'elsemployees_name', 'elsemployees_roleid', 'elsemployees_image')->get();
        return response()->json([
            'contacts' => $contacts_total,/*'Your contact list is empty',*/
        ], $this->successStatus);
    }
    public function getApproveUsers(Request $request)
    {
        $contacts_total = DB::table('elsemployees')->where('elsemployees_status', 2)->select('elsemployees_empid', 'elsemployees_name', 'elsemployees_roleid', 'elsemployees_image')->get();
        return response()->json([
            'contacts' => $contacts_total,/*'Your contact list is empty',*/
        ], $this->successStatus);
    }
    public function getDeclineUsers(Request $request)
    {
        $contacts_total = DB::table('elsemployees')->where('elsemployees_status', 1)->select('elsemployees_empid', 'elsemployees_name', 'elsemployees_roleid', 'elsemployees_image')->get();
        return response()->json([
            'contacts' => $contacts_total,/*'Your contact list is empty',*/
        ], $this->successStatus);
    }
    public function approveDeclineSignupUsers(Request $request)
    {
        $validate = Validator::make($request->all(), [ 
          'signupuser_id'    => 'required',
          'action'           => 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json("Fields Required", 400);
        }
        $status  = DB::table('elsemployees')
            ->where('elsemployees_empid','=',$request->signupuser_id)
            ->update([
            'elsemployees_status'         => $request->action,
        ]); 
        return response()->json(["message" => "Successfully Updated"], $this->successStatus);
    }
    public function deletemessage(Request $request)
    {
        $validate = Validator::make($request->all(), [ 
          'message_id'    => 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json("Fields Required", 400);
        }
        $status  = DB::table('message')
            ->where('message_id','=',$request->message_id)
            ->update([
            'status_id'         => 2,
        ]); 
        return response()->json(["message" => "Successfully Deleted"], $this->successStatus);
    }
    public function deletegroupmessage(Request $request)
    {
        $validate = Validator::make($request->all(), [ 
          'groupmessage_id'    => 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json("Fields Required", 400);
        }
        $status  = DB::table('groupmessage')
            ->where('groupmessage_id','=',$request->groupmessage_id)
            ->update([
            'status_id'         => 2,
        ]); 
        return response()->json(["message" => "Successfully Deleted"], $this->successStatus);
    }
    public function deletegroup(Request $request)
    {
        $validate = Validator::make($request->all(), [ 
          'group_id'    => 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json("Fields Required", 400);
        }
        $status  = DB::table('group')
            ->where('group_id','=',$request->group_id)
            ->update([
            'status_id'         => 2,
            'deleted_by'        => $request->user_id,
            'deleted_at'        => date('Y-m-d h:i:s'),
        ]); 
        DB::table('groupmember')
            ->where('group_id','=',$request->group_id)
            ->update([
            'status_id'         => 2,
        ]); 
        return response()->json(["message" => "Successfully Deleted"], $this->successStatus);
    }
    public function userdirectory(Request $request)
    {
        $users = DB::table('elsemployees')
        ->select('elsemployees_name','elsemployees_ext')
        ->where('elsemployees_status','=',2)
        ->get();     
        if ($users) {
            return Response::json([ 'users' => $users, $this->successStatus]);
        }
        else{
            $emptyarray = array();
            return Response::json([ 'users' => $emptyarray, $this->successStatus]);
        }
    }
    public function fetchAllSeenUsers(Request $request)
    {
        $getgroupmember = DB::table('groupmember')
        ->select('user_id')
        ->where('status_id','=',1)
        ->where('group_id','=',$request['group_id'])
        ->whereNotIn('user_id',[$request->groupmember_id])
        ->get();
        if (isset($getgroupmember)) {
        $getseenmsgid = array();
        $seenindex = 0;
        foreach ($getgroupmember as $getgroupmembers) {
            $getseenmsgid[$seenindex] = DB::table('groupmessageseen')
            ->join('elsemployees','elsemployees.elsemployees_empid', '=','groupmessageseen.user_id')
            ->select('groupmessage_id','user_id','elsemployees_image','elsemployees_name')
            ->where('group_id','=', $request['group_id'])
            ->where('user_id','=',$getgroupmembers->user_id)
            ->where('groupmessageseen_seen','=', 2)
            ->orderBy('groupmessageseen_id','DESC')
            ->first();
            $seenindex++;
        }
        $seendata = array();
        $seendataindex = 0;
        foreach ($getseenmsgid as $getseenmsgids) {
        if (isset($getseenmsgids->groupmessage_id)) {
            $seendata[$seendataindex]['messageid']      = $getseenmsgids->groupmessage_id;
            $seendata[$seendataindex]['userid']         = $getseenmsgids->user_id;
            $seendata[$seendataindex]['userpicture']    = $getseenmsgids->elsemployees_image;
            $seendata[$seendataindex]['username']       = $getseenmsgids->elsemployees_name;
            $seendataindex++;
        }else{
            $seendata[$seendataindex]['messageid']      = "";   
            $seendata[$seendataindex]['userid']         = "";   
            $seendata[$seendataindex]['userpicture']    = "";
            $seendata[$seendataindex]['username']       = "";
        }
        }
            return Response::json(['seendata' => $seendata, $this->successStatus]);
        }
        else{
            return Response::json(['seendata' =>  array(), $this->successStatus]);
        }
    }
    public function paginate($items, $perPage = 10, $page = null, $options = []){
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return  new  LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
    // Functions Use In Controller End
}