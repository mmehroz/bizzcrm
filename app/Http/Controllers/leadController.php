<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Image;
use DB;
use Input;
use App\Item;
use Session;
use Response;
use Validator;
use ZipArchive;

class leadController extends Controller
{
	public function createlead(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'lead_officenumber'  	=> 'required',
		    'lead_contactperson' 	=> 'required',
		    'lead_email' 			=> 'required',
		    'lead_designname' 		=> 'required',
		    'campaign_id' 			=> 'required',
		    'attachment'  			=>'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getleademail = DB::table('lead')
		->select('lead_email')
		->where('lead_email','=',$request->lead_email)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->first();
		if (isset($getleademail)) {
			return response()->json("Lead Email Already Exist", 400);
		}
		$lead_token = openssl_random_pseudo_bytes(7);
    	$lead_token = bin2hex($lead_token);
		$adds = array(
		'lead_companyname' 				=> $request->lead_companyname,
		'lead_contactperson'			=> $request->lead_contactperson,
		'lead_address' 					=> $request->lead_address,
		'lead_officenumber' 			=> $request->lead_officenumber,
		'lead_alternateofficenumber' 	=> $request->lead_alternateofficenumber,
		'lead_twitterid'				=> $request->lead_twitterid,
		'lead_facebookid' 				=> $request->lead_facebookid,
		'lead_instagramid' 				=> $request->lead_instagramid,
		'lead_state' 					=> $request->lead_state,
		'lead_city' 					=> $request->lead_city,
		'location_id'					=> $request->lead_location_id,
		'lead_timezone' 				=> $request->lead_timezone,
		'lead_email' 					=> $request->lead_email,
		'lead_alternateemail' 			=> $request->lead_alternateemail,
		'lead_website'					=> $request->lead_website,
		'lead_companyindustry' 			=> $request->lead_companyindustry,
		'lead_designation'				=> $request->lead_designation,
		'lead_companydescription'		=> $request->lead_companydescription,
		'lead_zipcode'					=> $request->lead_zipcode,
		'lead_deadlinedate'				=> $request->lead_deadlinedate,
		'lead_designname' 				=> $request->lead_designname,
		'lead_amountquoted' 			=> $request->lead_amountquoted,
		'lead_designtype'	 			=> $request->lead_designtype,
		'lead_placement' 				=> $request->lead_placement,
		'lead_requiredformat'			=> $request->lead_requiredformat,
		'lead_level'					=> $request->lead_level,
		'lead_fabric'					=> $request->lead_fabric,
		'lead_noofcolors'				=> $request->lead_noofcolors,
		'lead_colorblending' 			=> $request->lead_colorblending,
		'lead_backgroundfill' 			=> $request->lead_backgroundfill,
		'lead_height'	 				=> $request->lead_height,
		'lead_width' 					=> $request->lead_width,
		'lead_noofstitches'				=> $request->lead_noofstitches,
		'lead_instructions'				=> $request->lead_instructions,
		'lead_assignto'					=> "-1",
		'lead_date'						=> date('Y-m-d'),
		'lead_token'					=> $lead_token,
		'orderstatus_id' 				=> 2,
		'campaign_id' 					=> $request->campaign_id,
		'status_id'	 					=> 1,
		'created_by'		 			=> $request->user_id,
		'created_at'	 				=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('lead')->insert($adds);
		$images = $request->attachment;
    	$index = 0 ;
    	$filename = array();
    		foreach($images as $ima){
    			$saveattachment = array();
        		if( $ima->isValid()){
        			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $lead_token;
					$extension = $ima->getClientOriginalExtension();
		            $filename[$index] = $numb.$ima->getClientOriginalName();
		            $ima->move(public_path('lead/'.$foldername),$filename[$index]);
		            $filename[$index] = $numb.$ima->getClientOriginalName();
				  	$saveattachment[] = array(
					'attachment_name'		=> $filename[$index],
					'order_attachmenttoken'	=> $lead_token,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    	$index++;
        		}
        	DB::table('attachment')->insert($saveattachment);
        	}
        	$orderimages = $request->workings;
        	$orderindex = 0 ;
        	$orderfilename = array();
        	foreach($orderimages as $oi){
    			if( $oi->isValid()){
        			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $lead_token;
					$extension = $oi->getClientOriginalExtension();
		            $orderfilename[$orderindex] = $numb.$oi->getClientOriginalName();
		            $oi->move(public_path('order/'.$foldername),$orderfilename[$orderindex]);
		            $orderfilename[$orderindex] = $numb.$oi->getClientOriginalName();
			    	$orderindex++;
        		}
        	}
		if($save){
			return response()->json(['message' => 'Lead Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function leadlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	      'from'			=> 'required',
	      'to'				=> 'required',
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id <= 3) {
			$getorderlist = DB::table('lead')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('lead_managerpickby','=',null)
			->whereBetween('lead_date',[$request->from, $request->to])
			->where('status_id','=',1)
			->orderBy('lead_id','DESC')
			->get();	
		}else{
			$getorderlist = DB::table('lead')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->user_id)
			->whereBetween('lead_date',[$request->from, $request->to])
			->where('status_id','=',1)
			->orderBy('lead_id','DESC')
			->get();	
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Lead List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Lead List'],200);
		}
	}
	public function pickleadlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	      'from'			=> 'required',
	      'to'				=> 'required',
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('lead')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('lead_managerpickby','=',$request->user_id)
		->whereBetween('lead_date',[$request->from, $request->to])
		->whereIn('orderstatus_id',[2,35])
		->where('status_id','=',1)
		->orderBy('lead_id','DESC')
		->get();	
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Picked Lead List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Picked Lead List'],200);
		}
	}
	public function leaddetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'	=> 'required',
	      'lead_token'	=> 'required',
	      'lead_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('leaddetail')
		->select('*')
		->where('lead_id','=',$request->lead_id)
		->where('status_id','=',1)
		->orderBy('lead_id','DESC')
		->first();	
		$attachment = DB::table('attachment')
		->select('attachment_name','attachment_id')
		->where('order_attachmenttoken','=',$request->lead_token)
		->where('attachment_type','=',"client")
		->where('status_id','=',1)
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'attachmentdata' => $attachment,'message' => 'Lead Details'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Lead List'],200);
		}
	}
	public function savelead(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'freshlead_name'  		=> 'required',
		    'freshlead_email' 		=> 'required',
		    'freshlead_phone' 		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$adds = array(
		'freshlead_name' 			=> $request->freshlead_name,
		'freshlead_email'			=> $request->freshlead_email,
		'freshlead_phone' 			=> $request->freshlead_phone,
		'freshlead_otherdetail' 	=> $request->freshlead_otherdetail,
		'freshlead_date'			=> date('Y-m-d'),
		'campaign_id' 				=> $request->campaign_id,
		'status_id'	 				=> 1,
		'created_by'		 		=> $request->user_id,
		'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('freshlead')->insert($adds);
		if($save){
			return response()->json(['message' => 'Lead Saved Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function saveleadlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	      'from'			=> 'required',
	      'to'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
			$getorderlist = DB::table('freshlead')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('freshlead_date',[$request->from, $request->to])
			->where('status_id','=',1)
			->get();		
		}else{
			$getorderlist = DB::table('freshlead')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->user_id)
			->whereBetween('freshlead_date',[$request->from, $request->to])
			->where('status_id','=',1)
			->get();		
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Save Lead List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Lead List'],200);
		}
	}
	public function pickleadorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus = DB::table('lead')
			->where('lead_id','=',$request->lead_id)
			->update([
			'lead_managerpickby'	=> $request->user_id,
			'updated_by'			=> $request->user_id,
			'updated_at'			=> date('Y-m-d'),
		]);
		if($updateorderstatus){
			return response()->json(['message' => 'Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function unpickleadorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus = DB::table('lead')
			->where('lead_id','=',$request->lead_id)
			->update([
			'lead_managerpickby'	=> null,
			'updated_by'			=> $request->user_id,
			'updated_at'			=> date('Y-m-d'),
		]); 
		if($updateorderstatus){
			return response()->json(['message' => 'Unpick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatelead(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'lead_id'		 			=> 'required',		
	    	'lead_companyname' 			=> 'required',		
			'lead_contactperson'		=> 'required',		
			'lead_address' 				=> 'required',		
			'lead_officenumber' 		=> 'required',	
			'lead_alternateofficenumber'=> 'required',	
			'lead_twitterid'			=> 'required',	
			'lead_facebookid' 			=> 'required',		
			'lead_instagramid' 			=> 'required',	
			'lead_state' 				=> 'required',			
			'lead_city' 				=> 'required',				
			'lead_location_id'			=> 'required',				
			'lead_timezone' 			=> 'required',			
			'lead_email' 				=> 'required',				
			'lead_alternateemail'		=> 'required',			
			'lead_website'				=> 'required',	
			'lead_companyindustry' 		=> 'required',	
			'lead_designation'			=> 'required',	
			'lead_companydescription'	=> 'required',	
			'lead_zipcode'				=> 'required',			
			'lead_deadlinedate'			=> 'required',		
			'lead_designname' 			=> 'required',			
			'lead_amountquoted' 		=> 'required',		
			'lead_designtype'	 		=> 'required',			
			'lead_placement' 			=> 'required',			
			'lead_requiredformat'		=> 'required',	
			'lead_level'				=> 'required',				
			'lead_fabric'				=> 'required',				
			'lead_noofcolors'			=> 'required',			
			'lead_colorblending' 		=> 'required',		
			'lead_backgroundfill' 		=> 'required',		
			'lead_height'	 			=> 'required',			
			'lead_width' 				=> 'required',			
			'lead_noofstitches'			=> 'required',		
			'lead_instructions'			=> 'required',		
			'lead_assignto'				=> 'required',		
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$update = DB::table('lead')
		->where('lead_id','=',$request->lead_id)
		->update([
		'lead_companyname' 				=> $request->lead_companyname,
		'lead_contactperson'			=> $request->lead_contactperson,
		'lead_address' 					=> $request->lead_address,
		'lead_officenumber' 			=> $request->lead_officenumber,
		'lead_alternateofficenumber' 	=> $request->lead_alternateofficenumber,
		'lead_twitterid'				=> $request->lead_twitterid,
		'lead_facebookid' 				=> $request->lead_facebookid,
		'lead_instagramid' 				=> $request->lead_instagramid,
		'lead_state' 					=> $request->lead_state,
		'lead_city' 					=> $request->lead_city,
		'location_id'					=> $request->lead_location_id,
		'lead_timezone' 				=> $request->lead_timezone,
		'lead_email' 					=> $request->lead_email,
		'lead_alternateemail' 			=> $request->lead_alternateemail,
		'lead_website'					=> $request->lead_website,
		'lead_companyindustry' 			=> $request->lead_companyindustry,
		'lead_designation'				=> $request->lead_designation,
		'lead_companydescription'		=> $request->lead_companydescription,
		'lead_zipcode'					=> $request->lead_zipcode,
		'lead_deadlinedate'				=> $request->lead_deadlinedate,
		'lead_designname' 				=> $request->lead_designname,
		'lead_amountquoted' 			=> $request->lead_amountquoted,
		'lead_designtype'	 			=> $request->lead_designtype,
		'lead_placement' 				=> $request->lead_placement,
		'lead_requiredformat'			=> $request->lead_requiredformat,
		'lead_level'					=> $request->lead_level,
		'lead_fabric'					=> $request->lead_fabric,
		'lead_noofcolors'				=> $request->lead_noofcolors,
		'lead_colorblending' 			=> $request->lead_colorblending,
		'lead_backgroundfill' 			=> $request->lead_backgroundfill,
		'lead_height'	 				=> $request->lead_height,
		'lead_width' 					=> $request->lead_width,
		'lead_noofstitches'				=> $request->lead_noofstitches,
		'lead_instructions'				=> $request->lead_instructions,
		'lead_assignto'					=> $request->lead_assignto,
		'updated_by'		 			=> $request->user_id,
		'updated_at'	 				=> date('Y-m-d h:i:s'),
		]);
		if ($request->attachment != "undefined") {
		$images = $request->attachment;
    	$index = 0 ;
    	$filename = array();
    		foreach($images as $ima){
    			$saveattachment = array();
        		if( $ima->isValid()){
        			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $request->lead_token;
					$extension = $ima->getClientOriginalExtension();
		            $filename[$index] = $ima->getClientOriginalName();
		            $filename[$index] = $ima->move(public_path('lead/'.$foldername),$filename[$index]);
		            $filename[$index] = $ima->getClientOriginalName();
				  	$saveattachment[] = array(
					'attachment_name'		=> $filename[$index],
					'order_attachmenttoken'	=> $request->lead_token,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    	$index++;
        		}
        	DB::table('attachment')->insert($saveattachment);
        	}
        }
        if ($request->workings != "undefined") {
		$imagesorder = $request->workings;
    	$indexorder = 0 ;
    	$orderfilename = array();
    		foreach($imagesorder as $ima){
    			$saveattachment = array();
        		if( $ima->isValid()){
        			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $request->lead_token;
					$extension = $ima->getClientOriginalExtension();
		            $orderfilename[$indexorder] = $ima->getClientOriginalName();
		            $orderfilename[$indexorder] = $ima->move(public_path('order/'.$foldername),$orderfilename[$indexorder]);
		            $orderfilename[$indexorder] = $ima->getClientOriginalName();
					$indexorder++;
        		}
        	}
        }
		if($update){
			return response()->json(['message' => 'Lead Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function processlead(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'			=> 'required',
	      'orderstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json("Fields Required", 400);
		}
		if ($request->orderstatus_id == 4) {
		$leaddetail = DB::table('leaddetail')
		->select('*')
		->where('lead_id','=',$request->lead_id)
		->where('status_id','=',1)
		->orderBy('lead_id','DESC')
		->first();	
		$getclientemail = DB::table('client')
		->select('client_email')
		->where('client_email','=',$leaddetail->lead_email)
		->where('status_id','=',1)
		->where('campaign_id','=',$leaddetail->campaign_id)
		->first();
		if (isset($getclientemail)) {
			return response()->json("Client Email Already Exist", 400);
		}
		$client = array(
		'client_companyname' 			=> $leaddetail->lead_companyname,
		'client_contactperson'			=> $leaddetail->lead_contactperson,
		'client_address' 				=> $leaddetail->lead_address,
		'client_officenumber' 			=> $leaddetail->lead_officenumber,
		'client_alternateofficenumber' 	=> $leaddetail->lead_alternateofficenumber,
		'client_twitterid'				=> $leaddetail->lead_twitterid,
		'client_facebookid' 			=> $leaddetail->lead_facebookid,
		'client_instagramid' 			=> $leaddetail->lead_instagramid,
		'client_state' 					=> $leaddetail->lead_state,
		'client_city' 					=> $leaddetail->lead_city,
		'location_id'					=> $leaddetail->location_id,
		'client_timezone' 				=> $leaddetail->lead_timezone,
		'client_email' 					=> $leaddetail->lead_email,
		'client_alternateemail' 		=> $leaddetail->lead_alternateemail,
		'client_website'				=> $leaddetail->lead_website,
		'client_companyindustry' 		=> $leaddetail->lead_companyindustry,
		'client_designation' 			=> $leaddetail->lead_designation,
		'client_companydecription' 		=> $leaddetail->lead_companydescription,
		'client_zipcode' 				=> $leaddetail->lead_zipcode,
		'client_totalrevenue' 			=> "",
		'campaign_id'		 			=> $leaddetail->campaign_id,
		'status_id'		 				=> 1,
		'created_by'	 				=> $request->user_id,
		'created_at'	 				=> date('Y-m-d h:i:s'),
		);
		$saveclient = DB::table('client')->insert($client);
		$clientid = DB::getPdo()->lastInsertId();
		$order = array(
			'order_deadlinedate' 		=> $leaddetail->lead_deadlinedate,
			'order_designname'			=> $leaddetail->lead_designname,
			'order_amountquoted' 		=> $leaddetail->lead_amountquoted,
			'order_designtype' 			=> $leaddetail->lead_designtype,
			'order_placement' 			=> $leaddetail->lead_placement,
			'order_managerdescription'	=> $leaddetail->lead_requiredformat,
			'order_agentdescription' 	=> "",
			'order_requiredformat' 		=> $leaddetail->lead_requiredformat,
			'order_level' 				=> $leaddetail->lead_level,
			'order_fabric' 				=> $leaddetail->lead_fabric,
			'order_noofcolors'			=> $leaddetail->lead_noofcolors,
			'order_colorblending' 		=> $leaddetail->lead_colorblending,
			'order_backgroundfill' 		=> $leaddetail->lead_backgroundfill,
			'order_height' 				=> $leaddetail->lead_height,
			'order_width'				=> $leaddetail->lead_width,
			'order_noofstitches' 		=> $leaddetail->lead_noofstitches,
			'order_instructions'		=> $leaddetail->lead_instructions,
			'order_assignto'			=> $leaddetail->lead_assignto,
			'order_token'				=> $leaddetail->lead_token,
			'order_attachmenttoken'		=> $leaddetail->lead_token,
			'order_status'				=> "Assigned",
			'client_id' 				=> $clientid,
			'campaign_id' 				=> $leaddetail->campaign_id,
			'orderstatus_id' 			=> $request->orderstatus_id,
			'order_date'	 			=> $leaddetail->lead_date,
			'status_id'		 			=> 1,
			'created_by'	 			=> $request->user_id,
			'created_at'	 			=> $leaddetail->lead_date.' '.date('h:i:s'),
			);
		$save = DB::table('order')->insert($order);
		}
		$updateorderstatus = DB::table('lead')
			->where('lead_id','=',$request->lead_id)
			->update([
			'orderstatus_id'	=> $request->orderstatus_id,
			'updated_by'		=> $request->user_id,
			'updated_at'		=> date('Y-m-d'),
		]);
		if($updateorderstatus){
			return response()->json(['message' => 'Lead Processed Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}