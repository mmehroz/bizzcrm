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

class orderController extends Controller
{
	public function createorder(Request $request){
		// dd($request);
		$validate = Validator::make($request->all(), [ 
	    	'client_id'  	=> 'required',
		    'order' 		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
			$order_token = openssl_random_pseudo_bytes(7);
    		$order_token = bin2hex($order_token);
			// $order_token = mt_rand(100000, 999999);
			$multiple = $request->order;
			foreach ($multiple as $multiples) {
			$assignto;
			$validate = Validator::make($multiples, [
			    	'attachment'  =>'required',
		    	]);
				if ($validate->fails()) {    
					return response()->json("Invalid Format", 400);
				}
			// print_r($multiples['deadlineDate']);die;
				if (!empty($multiples['designName'] && $multiples['amountQuoted'] && $multiples['designType'] && $multiples['attachment'])) {
				$order_attachmenttoken = $this->generateRandomString(100);
				if (isset($multiples['assign_to'])) {
					$assignto = $multiples['assign_to'];
				}else{
					$assignto = '';
				}
				$adds[] = array(
				'order_deadlinedate' 		=> $multiples['deadlineDate'],
				'order_designname'			=> $multiples['designName'],
				'order_amountquoted' 		=> $multiples['amountQuoted'],
				'order_designtype' 			=> $multiples['designType'],
				'order_placement' 			=> $multiples['placement'],
				'order_managerdescription'	=> $multiples['managerDescription'],
				'order_agentdescription' 	=> $multiples['agentDescription'],
				// 'order_ponumber'			=> $multiples['poNumber'],
				'order_requiredformat' 		=> $multiples['requiredFormat'],
				'order_level' 				=> $multiples['level'],
				'order_fabric' 				=> $multiples['fabric'],
				'order_noofcolors'			=> $multiples['noofcolors'],
				'order_colorblending' 		=> $multiples['colorblending'],
				'order_backgroundfill' 		=> $multiples['backgroundFill'],
				'order_height' 				=> $multiples['height'],
				'order_width'				=> $multiples['width'],
				'order_noofstitches' 		=> $multiples['noOfStitches'],
				'order_instructions'		=> $multiples['instructions'],
				'order_assignto'			=> $assignto,
				'order_token'				=> $order_token,
				'order_attachmenttoken'		=> $order_attachmenttoken,
				'order_status'				=> $request->orderstatus_id == 4 ? "Assigned" : "Pending",
				'client_id' 				=> $request->client_id,
				'campaign_id' 				=> $getcampaignid->campaign_id,
				'orderstatus_id' 			=> $request->orderstatus_id,
				'order_date'	 			=> $multiples['order_date'],
				'status_id'		 			=> 1,
				'created_by'	 			=> $request->user_id,
				'created_at'	 			=> $multiples['order_date'].' '.date('h:i:s'),
				);
				$images = $multiples['attachment'];
		        	$index = 0 ;
		        	$filename = array();
		        		foreach($images as $ima){
		        			$saveattachment = array();
		            		if( $ima->isValid()){
		            			$number = rand(1,999);
						        $numb = $number / 7 ;
						        $foldername = $order_attachmenttoken;
								$extension = $ima->getClientOriginalExtension();
					            $filename[$index] = $ima->getClientOriginalName();
					            $filename[$index] = $ima->move(public_path('order/'.$foldername),$filename[$index]);
					            $filename[$index] = $ima->getClientOriginalName();
							  	$saveattachment[] = array(
								'attachment_name'		=> $filename[$index],
								'order_attachmenttoken'	=> $order_attachmenttoken,
								'status_id' 			=> 1,
								'created_by'			=> $request->user_id,
								'created_at'			=> date('Y-m-d h:i:s'),
								);
						    	$index++;
		            		}
		            	DB::table('attachment')->insert($saveattachment);
		            	}
		    }else{
				return response()->json("Design Name, Amount Quoted, Design Type & Attachment Are Required", 400);
			}
		    }
		    $save = DB::table('order')->insert($adds);

		if($save){
			return response()->json(['data' => $adds,'order_token' => $order_token,'message' => 'Order Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public  function generateRandomString($length = 20){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
	}
	public function updateorder(Request $request){
		// return response()->json($request);	
		$validatepicture = Validator::make($request->all(), [ 
	    	'attachment_token'  	  => 'required',
	    ]);
		if ($validatepicture->fails()) {    
			return response()->json("Attachment Token Required", 400);
		}
		if (!empty($request->attachment)) {
		$checkattaacment = DB::table('attachment')
			->select('attachment_id')
			->where('order_attachmenttoken','=',$request->attachment_token)
			->where('status_id','=',1)
			->count();
		if ($checkattaacment == 0) {
			return response()->json("Attachment Required", 400);	
		}
		}
		// $checkallorderstatusindeal = DB::table('order')
		// 	->select('order_status')
		// 	->whereIn('order_status',["Assigned","In Progress"])
		// 	->where('status_id','=',1)
		// 	->where('order_id','=',$request->order_id)
		// 	->first();
		// if (isset($checkallorderstatusindeal)) {
		// 	return response()->json("Edit Not Alowed", 400);
		// }
		$validate = Validator::make($request->all(), [ 
		      'order_id'	 		=> 'required',
		      'deadlineDate' 		=> 'required',
		      'designName'			=> 'required',
		      'amountQuoted' 		=> 'required',
		      'designType'			=> 'required',
		      'placement' 			=> 'required',
		      'managerDescription'	=> 'required',
		      'agentDescription' 	=> 'required',
		      'requiredFormat'		=> 'required',
		      'level'				=> 'required',
		      'fabric' 				=> 'required',
		      'noofcolors'			=> 'required',
		      'colorblending' 		=> 'required',
		      'backgroundFill'		=> 'required',
		      'height' 				=> 'required',
		      'width'				=> 'required',
		      'noOfStitches' 		=> 'required',
		      'instructions'		=> 'required',
		      'order_date'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
				$updateorder  = DB::table('order')
				->where('order_id','=',$request->order_id)
				->update([
				'order_deadlinedate' 		=> $request->deadlineDate,
				'order_designname'			=> $request->designName,
				'order_amountquoted' 		=> $request->amountQuoted,
				'order_designtype' 			=> $request->designType,
				'order_placement' 			=> $request->placement,
				'order_managerdescription'	=> $request->managerDescription,
				'order_agentdescription' 	=> $request->agentDescription,
				'order_requiredformat' 		=> $request->requiredFormat,
				'order_level' 				=> $request->level,
				'order_fabric' 				=> $request->fabric,
				'order_noofcolors'			=> $request->noofcolors,
				'order_colorblending' 		=> $request->colorblending,
				'order_backgroundfill' 		=> $request->backgroundFill,
				'order_height' 				=> $request->height,
				'order_width'				=> $request->width,
				'order_noofstitches' 		=> $request->noOfStitches,
				'order_instructions'		=> $request->instructions,
				'order_date'				=> $request->order_date,
				'order_assignto'			=> $request->assign_to ? $request->assign_to : '',
				// 'order_status'				=> $request->orderstatus_id == 4 ? "Assigned" : "Pending",
				'updated_by'	 			=> $request->user_id,
				'updated_at'	 			=> date('Y-m-d h:i:s'),
				]);
				$checkallorderstatusifassign = DB::table('order')
					->select('order_status')
					->where('order_status','=',"Pending")
					->where('status_id','=',1)
					->where('order_token','=',$request->order_token)
					->count();
				if ($checkallorderstatusifassign == 0) {
					 DB::table('order')
						->where('order_token','=',$request->order_token)
						->update([
						'orderstatus_id' 	=> $request->orderstatus_id,
						]);
				}
				if (!empty($request->order_status)) {
					DB::table('order')
						->where('order_id','=',$request->order_id)
						->update([
						'order_status' 	=> $request->order_status,
						]);
				}
				if (!empty($request->attachment)) {
					$validate = Validator::make($request->all(), [ 
				    	'attachment.*'=>'mimes:jpeg,bmp,png,jpg,ai,pdf,psd,eps,cdr,dst,emb,jdg,ofm,pxf,pes,JPEG,BMP,PNG,JPG,AI,PDF,PSD,EPS,CDR,DST,EMB,JDG,OFM,PXF,PES,doc,docx,heic',
			    	]);
					if ($validate->fails()) {    
						return response()->json("Invalid Format", 400);
					}
					$images = $request->attachment;
			        	$index = 0 ;
			        	$filename = array();
			        	if ($request->has('attachment')) {
			        		foreach($images as $ima){
			        			$saveattachment = array();
			            		if( $request->attachment[$index]->isValid()){
			            			$number = rand(1,999);
							        $numb = $number / 7 ;
							        $foldername = $request->attachment_token;
									$extension = $ima->getClientOriginalExtension();
						            $filename[$index]  	= $ima->getClientOriginalName();
						            $filename[$index] 	= $ima->move(public_path('order/'.$foldername),$filename[$index]);
						            $filename[$index]  	= $ima->getClientOriginalName();
								  	$saveattachment[] 	= array(
									'attachment_name'		=> $filename[$index],
									'order_attachmenttoken'	=> $request->attachment_token,
									'status_id' 			=> 1,
									'created_by'			=> $request->user_id,
									'created_at'			=> date('Y-m-d h:i:s'),
									);
							    	$index++;
			            		}
			            	DB::table('attachment')->insert($saveattachment);
			            	}
			            }
			            else{
			            	        $filename = 'no_image.png'; 
			                }
				}
		if($updateorder){
			return response()->json(['message' => 'Order Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deallist(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		      'typeofget'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getorderlist;
		$getsurrentmonth = date('n');
		if ($request->role_id == 1 && $request->type == "Monthly" || $request->role_id == 9 && $request->type == "Monthly") {
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('getdeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('created_at','like','%'.$getsurrentmonth.'%')
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();		
		}else{
		$getorderlist = DB::table('getdeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('created_at','like','%'.$getsurrentmonth.'%')
		->where('order_id','<',$request->lastid)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();	
		}
		}elseif ($request->role_id == 1 && $request->type == "All" || $request->role_id == 9 && $request->type == "All") {
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('getdeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();	
		}else{
		$getorderlist = DB::table('getdeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_id','<',$request->lastid)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();	
		}
		}else{
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('agentdeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('agentdeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}
		$lastindex = end($getorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'lastdata' => $lastindex,'message' => 'Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Deal List'],200);
		}
	}
	public function managerunpaiddeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		      'typeofget'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 9){
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}else{
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}
		$lastindex = end($getorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'lastdata' => $lastindex,'message' => 'Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function managerforwardedunpaiddeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		      'typeofget'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 9){
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}else{
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('order_managerpickby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('order_managerpickby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}
		$lastindex = end($getorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$request->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'lastdata' => $lastindex,'message' => 'Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function managerpaiddeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		      'typeofget'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if($request->role_id == 1 || $request->role_id == 2|| $request->role_id == 9){
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}else{
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}
		$lastindex = end($getorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'lastdata' => $lastindex,'message' => 'Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function managerforwardedpaiddeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		      'typeofget'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 9){
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}else{
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('order_managerpickby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('order_managerpickby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}
		$lastindex = end($getorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'lastdata' => $lastindex,'message' => 'Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
		public function managerrecoverydeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		      'typeofget'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 9){
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}else{
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}
		$lastindex = end($getorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'lastdata' => $lastindex,'message' => 'Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function managerforwardedrecoverydeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		      'typeofget'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 9){
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}else{
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('order_managerpickby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('order_managerpickby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}
		$lastindex = end($getorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'lastdata' => $lastindex,'message' => 'Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function managercanceldeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		      'typeofget'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 9){
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}else{
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}
		$lastindex = end($getorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'lastdata' => $lastindex,'message' => 'Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function managerforwardedcanceldeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		      'typeofget'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 9){
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}else{
		if ($request->typeofget == "New") {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('order_managerpickby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->where('order_id','<',$request->lastid)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('order_managerpickby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get()->toArray();
		}
		}
		$lastindex = end($getorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'lastdata' => $lastindex,'message' => 'Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function forwardeddeallist(Request $request){
		$getcampaignid = DB::table('user')
		->select('campaign_id')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->first();
		$getforwardedorderlist = DB::table('getdeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',2)
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		// $forwardedorders = array();
		// $index = 0;
		// foreach ($getforwardedorderlist as $getforwardedorderlist) {
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getforwardedorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getforwardedorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getforwardedorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getforwardedorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getforwardedorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getforwardedorderlist->completeordercount = $getcompletedorderindeal;
		// $forwardedorders[$index] = $getforwardedorderlist;
		// $index++;
		// }
		if($getforwardedorderlist){
			return response()->json(['data' => $getforwardedorderlist,'message' => 'Forwarded Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Forwarded Deal Not Available'],200);
		}
	}
	public function pickdeallist(Request $request){
		$getcampaignid = DB::table('user')
		->select('campaign_id')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->first();
		$getpickorderlist = DB::table('getdeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_managerpickby','=',$request->user_id)
		->whereIn('orderstatus_id', [3,4])
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		// dd($getpickorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getpickorderlist as $getpickorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getpickorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getpickorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getpickorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getpickorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getpickorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getpickorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getpickorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getpickorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getpickorderlist;
		// $index++;
		// }
		// dd($pickorders);
		if($getpickorderlist){
			return response()->json(['data' => $getpickorderlist,'message' => 'Pick Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Pick Deals Not Available'],200);
		}
	}
	public function deletedeal(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'order_token'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$updatecampaignstatus  = DB::table('order')
			->where('order_token','=',$request->order_token)
			->update([
			'status_id' 		=> 2,
			]); 
		if($updatecampaignstatus){
		return response()->json(['message' => 'Order Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function orderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'order_token'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getorderlist = DB::table('getorderlist')
		->select('*')
		->where('order_token','=',$request->order_token)
		->where('orderstatus_id','!=',12)
		->where('status_id','=',1)
		->get();
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function cancelorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'order_token'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getorderlist = DB::table('getorderlist')
		->select('*')
		->where('order_token','=',$request->order_token)
		->where('orderstatus_id','=',12)
		->where('status_id','=',1)
		->get();
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deleteorder(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'order_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$updatecampaignstatus  = DB::table('order')
			->where('order_id','=',$request->order_id)
			->update([
			'status_id' 		=> 2,
			]); 
		if($updatecampaignstatus){
		return response()->json(['message' => 'Order Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function workorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getassignedorderlist;
		if ($request->type == "Pick") {
			if ($request->role_id == 5) {
			$getassignedorderlist = DB::table('getorderlist')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','!=',12)
			->where('order_designtype','=',"Vector")
			->where('order_status','=',"In Progress")
			->where('order_workpickby','=',$request->user_id)
			->where('status_id','=',1)
			->orderBy('order_id','DESC')
			->limit(50)
			->get();
			}else{
			$getassignedorderlist = DB::table('getorderlist')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','!=',12)
			->where('order_designtype','=',"Digitize")
			->where('order_status','=',"In Progress")
			->where('order_workpickby','=',$request->user_id)
			->where('status_id','=',1)
			->orderBy('order_id','DESC')
			->limit(50)
			->get();
			}
		}else{
			if ($request->role_id == 5) {
			$getassignedorderlist = DB::table('getorderlist')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','!=',12)
			->where('order_status','=',"Assigned")
			->where('order_designtype','=',"Vector")
			->where('status_id','=',1)
			->orderBy('order_id','DESC')
			->limit(50)
			->get();
			}else{
			if ($request->user_id == 1) {
				$assignto = "1";
			}else{
				$assignto = "-1";
			}
			$getassignedorderlist = DB::table('getorderlist')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','!=',12)
			->where('order_status','=',"Assigned")
			->where('order_designtype','=',"Digitize")
			->where('order_assignto','=',$assignto)
			->where('status_id','=',1)
			->orderBy('order_id','DESC')
			->limit(50)
			->get();
			}
		}
		if($getassignedorderlist){
		return response()->json(['data' => $getassignedorderlist,'message' => 'Order List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function orderdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'order_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getorderdetails = DB::table('getorderdetails')
		->select('*')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->first();
		$getorderattachmentclient = DB::table('attachment')
		->select('attachment_name','attachment_id')
		->where('order_attachmenttoken','=',$getorderdetails->order_attachmenttoken)
		->where('attachment_type','=',"client")
		->where('status_id','=',1)
		->get();

		$getorderattachmentworker = DB::table('attachment')
		->select('attachment_name','attachment_id')
		->where('order_attachmenttoken','=',$getorderdetails->order_attachmenttoken)
		->where('attachment_type','=',"worker")
		->where('status_id','=',1)
		->get();

		$geteditordernote = DB::table('editnotes')
		->select('*')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->get();
		// dd($geteditordernote);
		// $orderdatailwitheditnote = array();
		// $index=0;
		// foreach ($geteditordernote as $geteditordernotes) {
		// 	$getorderdetails->editnote[$index] = $geteditordernotes->clientedit_instruction;
		// 	$getorderdetails->amount[$index] = $geteditordernotes->clientedit_amount;
		// $index++;
		// }
		$getorderattachmentclient = DB::table('attachment')
		->select('attachment_name','attachment_id')
		->where('order_attachmenttoken','=',$getorderdetails->order_attachmenttoken)
		->where('attachment_type','=',"client")
		->where('status_id','=',1)
		->get();

		$getorderattachmentedit = DB::table('attachment')
		->select('attachment_name','attachment_id','clientedit_id')
		->where('order_attachmenttoken','=',$getorderdetails->order_attachmenttoken)
		->where('attachment_type','=',"edit")
		->where('status_id','=',1)
		->get();

		if($getorderdetails){
		return response()->json(['data' => $getorderdetails,'attachmentdata' => $getorderattachmentclient,'attachmentdataworker' => $getorderattachmentworker,'editordernote' => $geteditordernote,'attachmentdataedit' => $getorderattachmentedit,'message' => 'Order Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateorderstatusagent(Request $request){
		$updateorderstatus = 0;
		$validate = Validator::make($request->all(), [ 
	      'orderstatus_id'		=> 'required',
	      'order_token'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$checkallorderstatusifcompleted = DB::table('order')
			->select('order_status')
			->where('order_status','!=',"Return To Agent")
			->where('order_status','!=',"Sent To Client")
			->where('order_status','!=',"Completed")
			->where('order_status','!=',"Edit By Client")
			->where('status_id','=',1)
			->where('order_token','=',$request->order_token)
			->count();
			if ($checkallorderstatusifcompleted == 0) {
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 	=> $request->orderstatus_id,
			]);
			}
		if($updateorderstatus){
		return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Orders Should Be Completed Before Forwarded To Billing", 400);
		}
	}
	public function updateorderstatusworker(Request $request){
		$updateorderstatus;
		$validate = Validator::make($request->all(), [ 
	      'order_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->type == "attachment") {
			$validate = Validator::make($request->all(), [ 
		      	'order_token'			=> 'required',
		      	'client_id'				=> 'required',
		      	'attachment_token'  	=> 'required',
		      	'attachment'  			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			if (!empty($request->attachment)) {
					// $validate = Validator::make($request->all(), [ 
				 //    	'attachment.*'=>'mimes:DST, jpeg,bmp,png,jpg,ai,pdf,psd,eps,cdr,dst,emb,jdg,ofm,pxf,pes,JPEG,BMP,PNG,JPG,AI,PDF,PSD,EPS,CDR,EMB,JDG,OFM,PXF,PES',
			  //   	]);
					// if ($validate->fails()) {    
					// 	return response()->json("Invalid Format", 400);
					// }
					$images = $request->attachment;
			        	$index = 0 ;
			        	$filename = array();
			        	if ($request->has('attachment')) {
			        		foreach($images as $ima){
			        			$saveattachment = array();
			            		if( $request->attachment[$index]->isValid()){
			            			$number = rand(1,999);
							        $numb = $number / 7 ;
							        $foldername = $request->attachment_token;
									$extension = $ima->getClientOriginalExtension();
						            $filename[$index]  = $ima->getClientOriginalName();
						            $filename[$index] = $ima->move(public_path('workorder/'.$foldername),$filename[$index]);
						            $filename[$index]  = $ima->getClientOriginalName();
								  	$saveattachment[] = array(
									'attachment_name'		=> $filename[$index],
									'attachment_type'		=> "worker",
									'order_attachmenttoken'	=> $request->attachment_token,
									'status_id' 			=> 1,
									'created_by'			=> $request->user_id,
									'created_at'			=> date('Y-m-d h:i:s'),
									);
							    	$index++;
			            		}
			            	DB::table('attachment')->insert($saveattachment);
			            	}
			            }
			            else{
			            	        $filename = 'no_image.png'; 
			                }
				}
			$checkallorderstatusifcompleted = DB::table('order')
			->select('order_status')
			->where('order_status','!=',"Completed")
			->where('status_id','=',1)
			->where('order_token','=',$request->order_token)
			->count();
			$checkallorderstatus = DB::table('order')
			->select('order_status')
			->where('status_id','=',1)
			->where('order_token','=',$request->order_token)
			->count();
			$checkallcompletedorderstatus = DB::table('order')
			->select('order_status')
			->where('order_status','=',"Completed")
			->where('status_id','=',1)
			->where('order_token','=',$request->order_token)
			->count();
			if ($checkallcompletedorderstatus = $checkallorderstatus) {
				DB::table('order')
					->where('order_token','=',$request->order_token)
					->update([
					'orderstatus_id' 		=> $request->orderstatus_id,
				]); 
			}
			if ($checkallorderstatus > $checkallcompletedorderstatus) {
				DB::table('order')
					->where('order_token','=',$request->order_token)
					->update([
					'orderstatus_id' 	=> 16,
				]); 
			}
			$checkallorderstatusindeal = DB::table('order')
			->select('order_status')
			->whereIn('order_status',["In Progress","Assign Edit"])
			->where('status_id','=',1)
			->where('order_id','=',$request->order_id)
			->first();
			if (isset($checkallorderstatusindeal)) {
				$updateorderstatus  = DB::table('order')
					->where('order_id','=',$request->order_id)
					->update([
					'order_status' 		=> "Completed",
				]); 
			}
		}else{
			$checkallorderstatusindeal = DB::table('order')
			->select('order_status')
			->where('order_status','=',"Assigned")
			->where('status_id','=',1)
			->where('order_id','=',$request->order_id)
			->first();
			if (isset($checkallorderstatusindeal)) {
				$updateorderstatus  = DB::table('order')
					->where('order_id','=',$request->order_id)
					->update([
					'order_status' 		=> "In Progress",
					'order_workpickby' 	=> $request->user_id,
				]); 
			}
		}
		if(isset($updateorderstatus)){
		return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateorderstatusmanager(Request $request){
		$updateorderstatus;
		$validate = Validator::make($request->all(), [ 
	      'orderstatus_id'		=> 'required',
	      'order_token'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$checkdealcreatedby = DB::table('order')
		->select('created_by')
		->where('order_token','=',$request->order_token)
		->where('created_by','=',$request->user_id)
		->count();
		if ($request->orderstatus_id == 3 && $checkdealcreatedby != 0) {
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 		=> $request->orderstatus_id,
				// 'order_managerpickby' 	=> $request->user_id,
			]); 
		}elseif ($request->orderstatus_id == 3 && $checkdealcreatedby == 0) {
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 		=> $request->orderstatus_id,
				'order_managerpickby' 	=> $request->user_id,
			]); 
		}else{
			$checkallorderstatusindeal = DB::table('order')
				->select('order_status')
				->where('order_token','=',$request->order_token)
				->whereIn('order_status',["Assigned","Pending","In Progress"])
				->where('status_id','=',1)
				->count();
			if ($checkallorderstatusindeal > 0) {
				return response()->json("Order Not Completed", 400);
			}
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 	=> $request->orderstatus_id,
				'order_managerdescription' 	=> $request->orderstatus_description,
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateorderstatusbilling(Request $request){
		$updateorderstatus;
		$validate = Validator::make($request->all(), [ 
	      'orderstatus_id'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if($request->orderstatus_id == 10){
		$validate = Validator::make($request->all(), [ 
	      'order_paypalinvoicenumber'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		}
		if ($request->type == "Merge") {
			$validate = Validator::make($request->all(), [ 
		      'mergedeal_token'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$getdeal = DB::table('mergedeal')
			->select('order_token')
			->where('mergedeal_token','=',$request->mergedeal_token)
			->where('created_by','=',$request->user_id)
			->where('status_id','=',1)
			->get();
			$getdealtoken = array();
			foreach ($getdeal as $getdeals) {
				$getdealtoken[] = $getdeals->order_token;
			}
			if($request->orderstatus_id == 9){
			$updateorderstatus  = DB::table('order')
				->whereIn('order_token',$getdealtoken)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
				'order_paypalinvoicenumber' => null,
			]); 
			DB::table('mergedeal')
				->where('mergedeal_token','=',$request->mergedeal_token)
				->update([
				'status_id' 	=> 2,
			]);
			}elseif($request->orderstatus_id == 10){
			$updateorderstatus  = DB::table('order')
				->whereIn('order_token',$getdealtoken)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
				'order_paypalinvoicenumber' => $request->order_paypalinvoicenumber,
			]); 
			}elseif($request->orderstatus_id == 18){
			$updateorderstatus  = DB::table('order')
				->whereIn('order_token',$getdealtoken)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
				'order_recoverydate' 		=> date('Y-m-d'),
			]); 
			}elseif($request->orderstatus_id == 11){
			$updateorderstatus  = DB::table('order')
				->whereIn('order_token',$getdealtoken)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
				'order_paiddate'	 		=> date('Y-m-d'),
			]); 
			}else{
			$updateorderstatus  = DB::table('order')
				->whereIn('order_token',$getdealtoken)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
			]); 
			}
		}else{
			$validate = Validator::make($request->all(), [ 
		      'order_token'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			if($request->orderstatus_id == 9){
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
				'order_paypalinvoicenumber' => null,
			]);
			}elseif($request->orderstatus_id == 10){
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
				'order_paypalinvoicenumber' => $request->order_paypalinvoicenumber,
			]);
			}elseif($request->orderstatus_id == 11){
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
				'order_paiddate' 			=> date('Y-m-d'),
			]);
			}elseif($request->orderstatus_id == 18){
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
				'order_recoverydate' 		=> date('Y-m-d'),
			]);
			}else{
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 			=> $request->orderstatus_id,
			]);
			}
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Invoice Sent Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deleteattachment(Request $request){
		$checkallorderstatusindeal = DB::table('order')
			->select('order_status')
			->where('order_status','!=',"Pending")
			->where('status_id','=',1)
			->where('order_id','=',$request->order_id)
			->first();
		if (isset($checkallorderstatusindeal)) {
			return response()->json("Edit Not Alowed", 400);
		}
		$validate = Validator::make($request->all(), [ 
		      'attachment_id'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$updateorderstatus  = DB::table('attachment')
			->where('attachment_id','=',$request->attachment_id)
			->update([
			'status_id' 		=> 2,
			]); 
		if($updateorderstatus){
		return response()->json(['message' => 'Attacment Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function randomsearchclient(Request $request){
		$getrandomclient = DB::table('getrandomclient')
		->select('*')
		->first();
		if($getrandomclient){
			return response()->json(['data' => $getrandomclient,'message' => 'Random Client Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}	
	}
	public function saveorcancilsearchclient(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'type'			=> 'required',
		      'rawclient_id'	=> 'required',
		      'comment'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			if ($request->type == "Save") {
				DB::table('rawclient')
				->where('rawclient_id','=',$request->rawclient_id)
				->update([
				'rawclient_contactperson' 			=> $request->rawclient_contactperson,
				'rawclient_companyname'				=> $request->rawclient_companyname,
				'rawclient_email'					=> $request->rawclient_email,
				'rawclient_alternateemail'			=> $request->rawclient_alternateemail,
				'rawclient_officenumber' 			=> $request->rawclient_officenumber,
				'rawclient_alternateofficenumber'	=> $request->rawclient_alternateofficenumber,
				'rawclient_state' 					=> $request->rawclient_state,
				'rawclient_city'					=> $request->rawclient_city,
				]); 
			}
		$updateorderstatus  = DB::table('rawclient')
			->where('rawclient_id','=',$request->rawclient_id)
			->update([
			'rawclient_status' 	=> $request->type,
			'rawclient_comment'	=> $request->comment,
			'created_by'		=> $request->user_id,
			'created_at'		=> date('Y-m-d h:i:s'),
			]); 
		if($updateorderstatus){
		return response()->json(['message' => 'Client '.$request->type.' Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function saveorcancilclientlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getsaveclientlist;
		$getcancelclientlist;
		if ($request->role_id == 1 || $request->role_id == 9) {
			$getsaveclientlist = DB::table('getrawclientdetails')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('rawclient_status','=',"Save")
			->where('status_id','=',1)
			->get();
			$getcancelclientlist = DB::table('getrawclientdetails')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('rawclient_status','=',"Cancel")
			->where('status_id','=',1)
			->get();
		}else{
			$getsaveclientlist = DB::table('getrawclientdetails')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->user_id)
			->where('rawclient_status','=',"Save")
			->where('status_id','=',1)
			->get();
			$getcancelclientlist = DB::table('getrawclientdetails')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->user_id)
			->where('rawclient_status','=',"Cancel")
			->where('status_id','=',1)
			->get();
		}
		if($getsaveclientlist || $getcancelclientlist){
			return response()->json(['savedata' => $getsaveclientlist,'canceldata' => $getcancelclientlist,'message' => 'Save Or Cancel Client List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}	
	}
	public function designerlist(Request $request){
		$getdesignerlist = DB::table('user')
		->select('user_id','user_name')
		->where('role_id','=',5)
		->where('status_id','=',1)
		->get();
		if($getdesignerlist){
			return response()->json(['data' => $getdesignerlist,'message' => 'Designer List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}	
	}
	public function digitizerlist(Request $request){
		$getdigitizerlist = DB::table('user')
		->select('user_id','user_name')
		->where('role_id','=',6)
		->where('status_id','=',1)
		->get();
		if($getdigitizerlist){
			return response()->json(['data' => $getdigitizerlist,'message' => 'Digitizer List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}	
	}
	public function updateorderstatutoedit(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_status'		=> 'required',
	      'order_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if(!empty($request->order_workernote)){
			DB::table('order')
				->where('order_id','=',$request->order_id)
				->update([
				'order_workernote'		=> $request->order_workernote,
			]); 
		}
		if(!empty($request->order_clienteditnote)){
			DB::table('order')
				->where('order_id','=',$request->order_id)
				->update([
				'order_clienteditnote'		=> $request->order_clienteditnote,
			]); 
		}
			$updateorderstatus  = DB::table('order')
				->where('order_id','=',$request->order_id)
				->update([
				'order_status' 			=> $request->order_status,
			]); 
		if($updateorderstatus){
		return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function editorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 3) {
		$getorderlist = DB::table('getorderlist')
		->select('*')
		->whereIn('order_status',["Agent Fixed","Back To Manager","Edit By Client"])
		->where('order_managerpickby','=',$request->user_id)
		->where('status_id','=',1)
		->get();	
		}elseif($request->role_id == 4){
		$getorderlist = DB::table('getorderlist')
		->select('*')
		->where('order_status','=',"Back To Agent")
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->get();
		}elseif($request->role_id == 5 || $request->role_id == 6){
		$getorderlist = DB::table('getorderlist')
		->select('*')
		->where('order_status','=',"Assign Edit")
		->where('order_workpickby','=',$request->user_id)
		->where('status_id','=',1)
		->get();
		}
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'message' => 'Edit Order List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function downloadclientattachment($foldername)
    {   
        $zip = new ZipArchive;
        $fileName = 'clientattachment.zip';
        if ($zip->open(public_path($fileName), ZipArchive::OVERWRITE) === TRUE)
        {
            $files = File::files(public_path('order/'.$foldername));
            foreach ($files as $file) {
                $relativeNameInZipFile = basename($file);
                $zip->addFile($file, $relativeNameInZipFile);
            }
            $zip->close();
        }
    	return response()->download(public_path($fileName));
    }
    public function downloadworkattachment($foldername)
    {   
        $zip = new ZipArchive;
        $fileName = 'workattachment.zip';
        if ($zip->open(public_path($fileName), ZipArchive::OVERWRITE) === TRUE)
        {
            $files = File::files(public_path('workorder/'.$foldername));
            foreach ($files as $file) {
                $relativeNameInZipFile = basename($file);
                $zip->addFile($file, $relativeNameInZipFile);
            }
            $zip->close();
        }
        return response()->download(public_path($fileName));
    }
    public function unpickorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if($request->role_id == 3){
			$validate = Validator::make($request->all(), [ 
		      'order_token'	=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$updateorderstatus = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id'		=> 2,
				'order_managerpickby'	=> null,
			]); 
		}
		if($request->role_id == 2){
			$validate = Validator::make($request->all(), [ 
		      'order_token'	=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$updateorderstatus = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id'		=> 8,
				'order_billingby'		=> null,
			]); 
		}
		if($request->role_id == 5 || $request->role_id == 6){
			$validate = Validator::make($request->all(), [ 
		      'order_id'	=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$updateorderstatus = DB::table('order')
				->where('order_id','=',$request->order_id)
				->update([
				'order_status'			=> "Assigned",
				'order_workpickby'		=> null,
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Unpick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
		}
		 public function pickorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if($request->role_id == 2){
			$validate = Validator::make($request->all(), [ 
		      'order_token'	=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$updateorderstatus = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id'		=> 9,
				'order_billingby'		=> $request->user_id,
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function completedorderlist(Request $request){
		$getcompleteorderlist = DB::table('getorderlist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereIn('order_status',["Completed","Return To Agent","Sent To Client","Back To Manager", "Edit By Client"])
		->where('order_workpickby','=',$request->user_id)
		->where('status_id','=',1)
		->orderBy('order_id','DESC')
		->get();
		if($getcompleteorderlist){
		return response()->json(['data' => $getcompleteorderlist,'message' => 'Completed Order List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function scrumboardforwardeddetails(Request $request){
		$getforwardedorder = DB::table('getscrumboarddetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',2)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get();
		// $forwardeddata = array();
		// $index = 0;
		// foreach ($getforwardedorder as $getforwardedorders) {
		// 	$getattachmentcount = DB::table('getattachmentcount')
		// 	->select('count')
		// 	->where('order_attachmenttoken','=',$getforwardedorders->order_attachmenttoken)
		// 	->first();	
		// 	$getforwardedorders->attchmentcount = $getattachmentcount->count;
		// 	$forwardeddata[$index] = $getforwardedorders;
		// 	$index++;
		// }
		// $indeex = 0;
		// foreach ($getforwardedorder as $getorders) {
		// 	$getordercount = DB::table('getscrumboarddetails')
		// 	->select('order_id')
		// 	->where('order_token','=',$getorders->order_token)
		// 	->count();	
		// 	$getorders->ordercount = $getordercount;
		// 	$forwardeddata[$indeex] = $getorders;
		// 	$indeex++;
		// }
		// if(isset($getforwardedorder)){
		return response()->json(['forwarded' => $getforwardedorder,'message' => 'Scrum Board Details'],200);
		// }else{
		// 	return response()->json("Oops! Something Went Wrong", 400);
		// }
	}
	public function scrumboardassigneddetails(Request $request){
		$getassignedorder = DB::table('getscrumboarddetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',4)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get();
		$assigneddata = array();
		// $index = 0;
		// foreach ($getassignedorder as $getassignedorders) {
		// 	$getattachmentcount = DB::table('getattachmentcount')
		// 	->select('count')
		// 	->where('order_attachmenttoken','=',$getassignedorders->order_attachmenttoken)
		// 	->first();	
		// 	$getassignedorders->attchmentcount = $getattachmentcount->count;
		// 	$assigneddata[$index] = $getassignedorders;
		// 	$index++;
		// }
		$indeex = 0;
		foreach ($getassignedorder as $getorders) {
			$getordercount = DB::table('getscrumboarddetails')
			->select('order_id')
			->where('order_token','=',$getorders->order_token)
			->count();	
			$getorders->ordercount = $getordercount;
			$assigneddata[$indeex] = $getorders;
			$indeex++;
		}
		$indeeex = 0;
		foreach ($getassignedorder as $getcompleteorders) {
			$getcompletecount = DB::table('getscrumboarddetails')
			->select('order_id')
			->where('order_status','=',"Completed")
			->where('order_token','=',$getcompleteorders->order_token)
			->count();	
			$getcompleteorders->completeordercount = $getcompletecount;
			$assigneddata[$indeeex] = $getcompleteorders;
			$indeeex++;
		}
		// if(isset($getassignedorder)){
		return response()->json(['data' => $assigneddata,'message' => 'Scrum Board Assinged Details'],200);
		// }else{
			// return response()->json("Oops! Something Went Wrong", 400);
		// }
	}
	public function scrumboardompleteddetails(Request $request){
		$getcompletedorder = DB::table('getscrumboarddetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',5)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get();
		// $completeddata = array();
		// $index = 0;
		// foreach ($getcompletedorder as $getcompletedorders) {
		// 	$getattachmentcount = DB::table('getattachmentcount')
		// 	->select('count')
		// 	->where('order_attachmenttoken','=',$getcompletedorders->order_attachmenttoken)
		// 	->first();	
		// 	$getcompletedorders->attchmentcount = $getattachmentcount->count;
		// 	$completeddata[$index] = $getcompletedorders;
		// 	$index++;
		// }
		// $indeex = 0;
		// foreach ($getcompletedorder as $getorders) {
		// 	$getordercount = DB::table('getscrumboarddetails')
		// 	->select('order_id')
		// 	->where('order_token','=',$getorders->order_token)
		// 	->count();	
		// 	$getorders->ordercount = $getordercount;
		// 	$completeddata[$indeex] = $getorders;
		// 	$indeex++;
		// }
		// if(isset($getcompletedorder)){
		return response()->json(['data' => $getcompletedorder,'message' => 'Scrum Board Completed Details'],200);
		// }else{
			// return response()->json("Oops! Something Went Wrong", 400);
		// }
	}
	public function scrumboardsentdetails(Request $request){
		$getsentorder = DB::table('getscrumboarddetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',7)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get();
		// $sentdata = array();
		// $index = 0;
		// foreach ($getsentorder as $getsentorders) {
		// 	$getattachmentcount = DB::table('getattachmentcount')
		// 	->select('count')
		// 	->where('order_attachmenttoken','=',$getsentorders->order_attachmenttoken)
		// 	->first();	
		// 	$getsentorders->attchmentcount = $getattachmentcount->count;
		// 	$sentdata[$index] = $getsentorders;
		// 	$index++;
		// }
		// $indeex = 0;
		// foreach ($getsentorder as $getorders) {
		// 	$getordercount = DB::table('getscrumboarddetails')
		// 	->select('order_id')
		// 	->where('order_token','=',$getorders->order_token)
		// 	->count();	
		// 	$getorders->ordercount = $getordercount;
		// 	$sentdata[$indeex] = $getorders;
		// 	$indeex++;
		// }
		// if(isset($getsentorder)){
		return response()->json(['data' => $getsentorder,'message' => 'Scrum Board Sent Details'],200);
		// }else{
			// return response()->json("Oops! Something Went Wrong", 400);
		// }
	}
	public function scrumboardbillingdetails(Request $request){
		$getbillingorder = DB::table('getscrumboarddetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',8)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get();
		// dd($getbillingorder);
		// $billingdata = array();
		// $index = 0;
		// foreach ($getbillingorder as $getbillingorders) {
		// 	$getattachmentcount = DB::table('getattachmentcount')
		// 	->select('count')
		// 	->where('order_attachmenttoken','=',$getbillingorders->order_attachmenttoken)
		// 	->first();	
		// 	$getbillingorders->attchmentcount = $getattachmentcount->count;
		// 	$billingdata[$index] = $getbillingorders;
		// 	$index++;
		// }
		// $indeex = 0;
		// foreach ($getbillingorder as $getorders) {
		// 	$getordercount = DB::table('getscrumboarddetails')
		// 	->select('order_id')
		// 	->where('order_token','=',$getorders->order_token)
		// 	->count();	
		// 	$getorders->ordercount = $getordercount;
		// 	$billingdata[$indeex] = $getorders;
		// 	$indeex++;
		// }
		// if(isset($getbillingorder)){
		return response()->json(['data' => $getbillingorder,'message' => 'Scrum Board Billing Details'],200);
		// }else{
			// return response()->json("Oops! Something Went Wrong", 400);
		// }
	}
	public function scrumboardpaiddetails(Request $request){
		$getpaidorder = DB::table('getscrumboarddetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',11)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('order_id','DESC')
		->limit(50)
		->get();
		// $paiddata = array();
		// $index = 0;
		// foreach ($getpaidorder as $getpaidorders) {
		// 	$getattachmentcount = DB::table('getattachmentcount')
		// 	->select('count')
		// 	->where('order_attachmenttoken','=',$getpaidorders->order_attachmenttoken)
		// 	->first();	
		// 	if($getattachmentcount != null){
		// 	$getpaidorders->attchmentcount = $getattachmentcount->count;
		// 	}else{
		// 	$getpaidorders->attchmentcount = 0;
		// 	}
		// 	$paiddata[$index] = $getpaidorders;
		// 	$index++;
		// }
		// $indeex = 0;
		// foreach ($getpaidorder as $getorders) {
		// 	$getordercount = DB::table('getscrumboarddetails')
		// 	->select('order_id')
		// 	->where('order_token','=',$getorders->order_token)
		// 	->count();	
		// 	$getorders->ordercount = $getordercount;
		// 	$paiddata[$indeex] = $getorders;
		// 	$indeex++;
		// }
		// if(isset($$getpaidorder)){
		return response()->json(['data' => $getpaidorder,'message' => 'Scrum Board Paid Details'],200);
		// }else{
			// return response()->json("Oops! Something Went Wrong", 400);
		// }
	}
	public function scrumboardeditdetails(Request $request){
		$geteditorder = DB::table('getscrumboarddetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_status','=',"Edit By Client")
		->where('status_id','=',1)
		->orderBy('order_id','DESC')
		->limit(50)
		->get();
		// $editdata = array();
		// $index = 0;
		// foreach ($geteditorder as $geteditorders) {
		// 	$getattachmentcount = DB::table('attachment')
		// 	->select('attachment_id')
		// 	->where('order_attachmenttoken','=',$geteditorders->order_attachmenttoken)
		// 	->count();	
		// 	$geteditorders->attchmentcount = $getattachmentcount;
		// 	$editdata[$index] = $geteditorders;
		// 	$index++;
		// }
		// if(isset($geteditorder)){
		return response()->json(['data' => $geteditorder,'message' => 'Scrum Board Edit Details'],200);
		// }else{
			// return response()->json("Oops! Something Went Wrong", 400);
		// }
	}
	public function billingdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'client_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getclientgross = DB::table('order')
		->select('order_amountquoted')
		->where('client_id','=',$request->client_id)
		->sum('order_amountquoted');

		$getclientpaid = DB::table('order')
		->select('order_amountquoted')
		->where('client_id','=',$request->client_id)
		->where('orderstatus_id','=',8)
		->sum('order_amountquoted');

		$getclientunpaid = DB::table('order')
		->select('order_amountquoted')
		->where('client_id','=',$request->client_id)
		->where('orderstatus_id','=',7)
		->sum('order_amountquoted');

		$getclientcancil = DB::table('order')
		->select('order_amountquoted')
		->where('client_id','=',$request->client_id)
		->where('orderstatus_id','=',12)
		->sum('order_amountquoted');
			return response()->json(['gross' => $getclientgross,'paid' => $getclientpaid,'unpaid' => $getclientunpaid,'cancel' => $getclientcancil,'message' => 'Billing Details'],200);
	}
	public function assignorder(Request $request){
		$updateorderstatus = 0;
		$validate = Validator::make($request->all(), [ 
	      'order_id'		=> 'required',
	      'order_token'		=> 'required',
	      'assign_to'		=> 'required',
	      'orderstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus  = DB::table('order')
				->where('order_id','=',$request->order_id)
				->update([
				'order_assignto' 	=> $request->assign_to,
				'order_status' 		=> "Assigned",
			]);
		$checkallorderstatusifpending = DB::table('order')
			->select('order_status')
			->where('order_status','=',"Pending")
			->where('status_id','=',1)
			->where('order_token','=',$request->order_token)
			->count();
			if ($checkallorderstatusifpending <= 0) {
			$updatedealstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'orderstatus_id' 	=> $request->orderstatus_id,
			]);
			}
		if($updateorderstatus){
			$getorderdetails = DB::table('getorderdetails')
			->select('*')
			->where('order_id','=',$request->order_id)
			->where('status_id','=',1)
			->first();
			return response()->json(['asignedorderdetails' => $getorderdetails,'message' => 'Order Assigned Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function notesonclientedit(Request $request){
		$validate = Validator::make($request->all(), [ 
		    'clientedit_instruction'	=> 'required',
		    'order_id'					=> 'required',
	      	'attachment_token'  		=> 'required',
	      	'order_status'		  		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields required", 400);
		}
				$saveedit[] = array(
					'clientedit_instruction'	=> $request->clientedit_instruction,
					'clientedit_amount'			=> $request->clientedit_amount,
					'order_attachmenttoken'		=> $request->attachment_token,
					'order_id'					=> $request->order_id,
					'status_id' 				=> 1,
					'created_by'				=> $request->user_id,
					'created_at'				=> date('Y-m-d h:i:s'),
					);
        	DB::table('clientedit')->insert($saveedit);
        	$edit_id = DB::getPdo()->lastInsertId();
			if (!empty($request->attachment)) {
					$validate = Validator::make($request->all(), [ 
				    	'attachment.*'=>'mimes:jpeg,bmp,png,jpg,ai,pdf,psd,eps,cdr,dst,emb,jdg,ofm,pxf,pes,JPEG,BMP,PNG,JPG,AI,PDF,PSD,EPS,CDR,DST,EMB,JDG,OFM,PXF,PES,doc,docx,heic',
			    	]);
					if ($validate->fails()) {    
						return response()->json("Invalid Format", 400);
					}
					$images = $request->attachment;
			        	$index = 0 ;
			        	$filename = array();
			        	if ($request->has('attachment')) {
			        		foreach($images as $ima){
			        			$saveattachment = array();
			            		if( $request->attachment[$index]->isValid()){
			            			$number = rand(1,999);
							        $numb = $number / 7 ;
							        $foldername = $request->attachment_token;
									$extension = $ima->getClientOriginalExtension();
						            $filename[$index]  = $ima->getClientOriginalName();
						            $filename[$index] = $ima->move(public_path('editorder/'.$foldername),$filename[$index]);
						            $filename[$index]  = $ima->getClientOriginalName();
								  	$saveattachment[] = array(
									'attachment_name'		=> $filename[$index],
									'attachment_type'		=> "edit",
									'order_attachmenttoken'	=> $request->attachment_token,
									'status_id' 			=> 1,
									'created_by'			=> $request->user_id,
									'created_at'			=> date('Y-m-d h:i:s'),
									'clientedit_id'			=> $edit_id,
									);
							    	$index++;
			            		}
			            	DB::table('attachment')->insert($saveattachment);
			            	}
			            }
			            else{
			            	        $filename = 'no_image.png'; 
			                }
						}
		            	$updateorderstatus  = DB::table('order')
							->where('order_id','=',$request->order_id)
							->update([
							'order_status' 			=> $request->order_status,
						]); 
		if(isset($saveedit)){
		return response()->json(['message' => 'Client Edit Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function sentordertoclient(Request $request){
		$updateorderstatus;
		$validate = Validator::make($request->all(), [ 
	      'orderstatus_id'		=> 'required',
	      'order_token'			=> 'required',
	      'order_id'			=> 'required',
	      'order_status'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->order_status == "Return To Agent") {
		$updateorderstatus  = DB::table('order')
			->where('order_id','=',$request->order_id)
			->update([
			'order_status' 		=> $request->order_status,
			'updated_by' 		=> $request->user_id,
		]); 
		}else{
		$updateorderstatus  = DB::table('order')
			->where('order_id','=',$request->order_id)
			->where('created_by','=',$request->user_id)
			->update([
			'order_status' 		=> $request->order_status,
			'updated_by' 		=> $request->user_id,
		]); 
		}
		$checkallorderstatusindeal = DB::table('order')
		->select('order_status')
		->where('order_token','=',$request->order_token)
		->whereIn('order_status',["Sent To Client"])
		->where('status_id','=',1)
		->count();
		if ($checkallorderstatusindeal > 0) {
		$updatedealstatus  = DB::table('order')
			->where('order_token','=',$request->order_token)
			->update([
			'orderstatus_id' 			=> $request->orderstatus_id,
		]); 
		}
		$getorderdetails = DB::table('getorderdetails')
		->select('*')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->first();
		if($updateorderstatus){
		return response()->json(['orderdetails' => $getorderdetails,'message' => 'Order Sent Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	// New updates for as per ahsan end
	// billing start
	public function billingforwardeddeallist(Request $request){
		$getcampaignid = DB::table('user')
		->select('campaign_id')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->first();
		$getforwardedorderlist = DB::table('completedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',8)
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		// $forwardedorders = array();
		// $index = 0;
		// foreach ($getforwardedorderlist as $getforwardedorderlist) {
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getforwardedorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getforwardedorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getforwardedorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getforwardedorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getforwardedorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getforwardedorderlist->completeordercount = $getcompletedorderindeal;
		// $forwardedorders[$index] = $getforwardedorderlist;
		// $index++;
		// }
		if($getforwardedorderlist){
			return response()->json(['data' => $getforwardedorderlist,'message' => 'Billing Forwarded Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Billing Forwarded Deal Not Available'],200);
		}
	}
	public function billingpickdeallist(Request $request){
		$getcampaignid = DB::table('user')
		->select('campaign_id')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->first();
		$getmergedeal = DB::table('mergedeal')
		->select('order_token')
		->where('status_id','=',1)
		->get();
		$getmergedealtoken = array();
		foreach ($getmergedeal as $getmergedeals) {
			$getmergedealtoken[] = $getmergedeals->order_token;
		}
		$getbillingpickorderlist = DB::table('completedeallist')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',9)
		// ->where('order_billingby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		// dd($getpickorderlist);
		// $pickorders = array();
		// $index = 0;
		// foreach ($getbillingpickorderlist as $getbillingpickorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getbillingpickorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getbillingpickorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getbillingpickorderlist->order_token)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getbillingpickorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getbillingpickorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getbillingpickorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getbillingpickorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getbillingpickorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getbillingpickorderlist;
		// $index++;
		// }
		// dd($pickorders);
		if($getbillingpickorderlist){
			return response()->json(['data' => $getbillingpickorderlist,'message' => 'Billing Pick Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Billing Pick Deals Not Available'],200);
		}
	}
	public function invoicedeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getmergedeal = DB::table('mergedeal')
		->select('order_token')
		->where('status_id','=',1)
		->get();
		$getmergedealtoken = array();
		foreach ($getmergedeal as $getmergedeals) {
			$getmergedealtoken[] = $getmergedeals->order_token;
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',10)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		$getsumorderlist = DB::table('completedeallist')
		->select('order_amountquoted')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',10)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',10)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		$getsumorderlist = DB::table('completedeallist')
		->select('order_amountquoted')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',10)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('orderstatus_id','!=',12)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist, 'sumorder' => $getsumorderlist, 'message' => 'Sent Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function paiddeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getmergedeal = DB::table('mergedeal')
		->select('order_token')
		->where('status_id','=',1)
		->get();
		$getmergedealtoken = array();
		foreach ($getmergedeal as $getmergedeals) {
			$getmergedealtoken[] = $getmergedeals->order_token;
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',11)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		$getsumorderlist = DB::table('completedeallist')
		->select('order_amountquoted')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',11)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',11)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		$getsumorderlist = DB::table('completedeallist')
		->select('order_amountquoted')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',11)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('orderstatus_id','!=',12)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist, 'sumorder' => $getsumorderlist, 'message' => 'Paid Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function canceldeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getmergedeal = DB::table('mergedeal')
		->select('order_token')
		->where('status_id','=',1)
		->get();
		$getmergedealtoken = array();
		foreach ($getmergedeal as $getmergedeals) {
			$getmergedealtoken[] = $getmergedeals->order_token;
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',12)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		$getsumorderlist = DB::table('completedeallist')
		->select('order_amountquoted')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',12)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',12)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		$getsumorderlist = DB::table('completedeallist')
		->select('order_amountquoted')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',12)
		->whereBetween('order_date', [$from, $to])
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('orderstatus_id','=',12)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist, 'sumorder' => $getsumorderlist, 'message' => 'Cancel Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function recoverydeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getmergedeal = DB::table('mergedeal')
		->select('order_token')
		->where('status_id','=',1)
		->get();
		$getmergedealtoken = array();
		foreach ($getmergedeal as $getmergedeals) {
			$getmergedealtoken[] = $getmergedeals->order_token;
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',18)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		$getsumorderlist = DB::table('completedeallist')
		->select('order_amountquoted')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',18)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}else{
		$getorderlist = DB::table('completedeallist')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',18)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->get();
		$getsumorderlist = DB::table('completedeallist')
		->select('order_amountquoted')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',18)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('orderstatus_id','!=',12)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist, 'sumorder' => $getsumorderlist, 'message' => 'Recovery Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function mergedealforbilling(Request $request){
		$mergedeal_token = mt_rand(1000000, 9999999);
			$multiple = $request->order_token;
			$index = 0;
			foreach ($multiple as $multiples) {
				$adds[] = array(
				'mergedeal_token' 	=> $mergedeal_token,
				'order_token' 		=> $request->order_token[$index],
				'client_id'			=> $request->client_id,
				'status_id'		 	=> 1,
				'created_by'	 	=> $request->user_id,
				'created_at'	 	=> date('Y-m-d h:i:s'),
				);
				$index++;
			}
		    $save = DB::table('mergedeal')->insert($adds);
		if($save){
			return response()->json(['message' => 'Deals Merge Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function mergedeallist(Request $request){
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getorderlist = DB::table('mergedeallist')
		->select('*')
		->where('mergestatus_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		// ->where('order_billingby','=',$request->user_id)
		->where('orderstatus_id','=',9)
		->where('status_id','=',1)
		->groupBy('mergedeal_token')
		->get();
		// $getdeatlist = DB::table('mergedeallist')
		// ->select('*')
		// ->where('campaign_id','=',$request->campaign_id)
		// ->where('order_billingby','=',$request->user_id)
		// ->where('status_id','=',1)
		// ->get();
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getdeal = DB::table('mergedeal')
		// ->select('order_token')
		// ->where('mergedeal_token','=',$getorderlist->mergedeal_token)
		// ->where('status_id','=',1)
		// ->get();
		// $getdealtoken = array();
		// foreach ($getdeal as $getdeals) {
		// 	$getdealtoken[] = $getdeals->order_token;
		// }
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->whereIn('order_token',$getdealtoken)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist,'message' => 'Merge Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function mergeorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getdeal = DB::table('mergedeal')
		->select('order_token')
		->where('mergedeal_token','=',$request->mergedeal_token)
		// ->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->get();
		$getdealtoken = array();
		foreach ($getdeal as $getdeals) {
			$getdealtoken[] = $getdeals->order_token;
		}
		// dd($getdealtoken);
		$getmergeorderlist = DB::table('getorderlist')
			->select('*')
			// ->where('order_billingby','=',$request->user_id)
			->where('orderstatus_id','!=',12)
			->whereIn('order_token',$getdealtoken)
			->where('status_id','=',1)
			->get();
		if($getmergeorderlist){
		return response()->json(['data' => $getmergeorderlist,'message' => 'Merge Order List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function mergecancelorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getdeal = DB::table('mergedeal')
		->select('order_token')
		->where('mergedeal_token','=',$request->mergedeal_token)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->get();
		$getdealtoken = array();
		foreach ($getdeal as $getdeals) {
			$getdealtoken[] = $getdeals->order_token;
		}
		// dd($getdealtoken);
		if ($request->role_id == 2) {
		$getmergeorderlist = DB::table('getorderlist')
		->select('*')
		->where('order_billingby','=',$request->user_id)
		->where('orderstatus_id','=',12)
		->whereIn('order_token',$getdealtoken)
		->where('status_id','=',1)
		->get();
		}else{
		$getmergeorderlist = DB::table('getorderlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->whereIn('order_token',$getdealtoken)
		->where('status_id','=',1)
		->get();
		}
		if($getmergeorderlist){
		return response()->json(['data' => $getmergeorderlist,'message' => 'Merge Order List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function mergeinvoicedeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('mergedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',10)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->groupBy('mergedeal_token')
		->get();
		$getsumorderlist = DB::table('mergedeallist')
		->select('order_amountquoted')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',10)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}else{
		$getorderlist = DB::table('mergedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',10)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->groupBy('mergedeal_token')
		->get();
		$getsumorderlist = DB::table('mergedeallist')
		->select('order_amountquoted')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',10)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getdeal = DB::table('mergedeal')
		// ->select('order_token')
		// ->where('mergedeal_token','=',$getorderlist->mergedeal_token)
		// ->where('status_id','=',1)
		// ->get();
		// $getdealtoken = array();
		// foreach ($getdeal as $getdeals) {
		// 	$getdealtoken[] = $getdeals->order_token;
		// }
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->whereIn('order_token',$getdealtoken)
		// ->where('orderstatus_id','!=',12)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist, 'sumorderamount' => $getsumorderlist,'message' => 'Merge Invoice Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function mergepaiddeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('mergedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('status_id','=',1)
		->where('mergestatus_id','=',1)
		->groupBy('mergedeal_token')
		->get();
		$getsumorderlist = DB::table('mergedeallist')
		->select('order_amountquoted')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}else{
		$getorderlist = DB::table('mergedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('status_id','=',1)
		->where('mergestatus_id','=',1)
		->groupBy('mergedeal_token')
		->get();
		$getsumorderlist = DB::table('mergedeallist')
		->select('order_amountquoted')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getdeal = DB::table('mergedeal')
		// ->select('order_token')
		// ->where('mergedeal_token','=',$getorderlist->mergedeal_token)
		// ->where('status_id','=',1)
		// ->get();
		// $getdealtoken = array();
		// foreach ($getdeal as $getdeals) {
		// 	$getdealtoken[] = $getdeals->order_token;
		// }
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->whereIn('order_token',$getdealtoken)
		// ->where('orderstatus_id','!=',12)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist, 'sumorderamount' => $getsumorderlist,'message' => 'Merge Paid Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function mergecanceldeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('mergedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->groupBy('mergedeal_token')
		->get();
		$getsumorderlist = DB::table('mergedeallist')
		->select('order_amountquoted')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->sum('order_amountquoted');	
		}else{
		$getorderlist = DB::table('mergedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->groupBy('mergedeal_token')
		->get();
		$getsumorderlist = DB::table('mergedeallist')
		->select('order_amountquoted')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('mergestatus_id','=',1)
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getdeal = DB::table('mergedeal')
		// ->select('order_token')
		// ->where('mergedeal_token','=',$getorderlist->mergedeal_token)
		// ->where('status_id','=',1)
		// ->get();
		// $getdealtoken = array();
		// foreach ($getdeal as $getdeals) {
		// 	$getdealtoken[] = $getdeals->order_token;
		// }
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->whereIn('order_token',$getdealtoken)
		// ->where('orderstatus_id','=',12)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// dd($getsumorder);
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist, 'sumorderamount' => $getsumorderlist,'message' => 'Merge Cancel Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	public function mergerecoverydeallist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		$getorderlist;
		$validate = Validator::make($request->all(), [ 
		      'role_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('mergedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('status_id','=',1)
		->where('mergestatus_id','=',1)
		->groupBy('mergedeal_token')
		->get();
		$getsumorderlist = DB::table('mergedeallist')
		->select('order_amountquoted')
		->where('campaign_id','=',$request->campaign_id)
		->where('order_billingby','=',$request->user_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('status_id','=',1)
		->where('mergestatus_id','=',1)
		->sum('order_amountquoted');
		}else{
		$getorderlist = DB::table('mergedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('status_id','=',1)
		->where('mergestatus_id','=',1)
		->groupBy('mergedeal_token')
		->get();
		$getsumorderlist = DB::table('mergedeallist')
		->select('order_amountquoted')
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('orderstatus_id','=',18)
		->where('status_id','=',1)
		->where('mergestatus_id','=',1)
		->sum('order_amountquoted');
		}
		// $pickorders = array();
		// $index = 0;
		// foreach ($getorderlist as $getorderlist) {
		// $geteditorder = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Back To Agent","Agent Fixed","Back To Manager","Edit By Client","Assign Edit"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->editordercount = $geteditorder;
		// $getdeal = DB::table('mergedeal')
		// ->select('order_token')
		// ->where('mergedeal_token','=',$getorderlist->mergedeal_token)
		// ->where('status_id','=',1)
		// ->get();
		// $getdealtoken = array();
		// foreach ($getdeal as $getdeals) {
		// 	$getdealtoken[] = $getdeals->order_token;
		// }
		// $getsumorder = DB::table('order')
		// ->select('order_amountquoted')
		// ->whereIn('order_token',$getdealtoken)
		// ->where('orderstatus_id','!=',12)
		// ->where('status_id','=',1)
		// ->sum('order_amountquoted');
		// $getorderlist->sumorderamount = $getsumorder;
		// $getallorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->allordercount = $getallorderindeal;
		// $getcompletedorderindeal = DB::table('order')
		// ->select('order_id')
		// ->where('order_token','=',$getorderlist->order_token)
		// ->whereIn('order_status',["Completed","Sent To Client"])
		// ->where('status_id','=',1)
		// ->count();
		// $getorderlist->completeordercount = $getcompletedorderindeal;
		// $pickorders[$index] = $getorderlist;
		// $index++;
		// }
		if($getorderlist){
		return response()->json(['data' => $getorderlist, 'sumorderamount' => $getsumorderlist,'message' => 'Merge Recovery Deal List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Create Deal To View'],200);
		}
	}
	// billing end
	public function cancelorder(Request $request){
		$updateorderstatus;
		$validate = Validator::make($request->all(), [ 
	      'order_id'			=> 'required',
	      'order_token'			=> 'required',
	      'orderstatus_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$cancelorder  = DB::table('order')
			->where('order_id','=',$request->order_id)
			->update([
			'order_status' 			=> "Cancel",
			'orderstatus_id' 		=> $request->orderstatus_id,
			'order_cancelcomment' 	=> $request->order_cancelcomment,
		]); 
		$checkorderindeal = DB::table('order')
		->select('order_id')
		->where('order_token','=',$request->order_token)
		->where('order_status','!=',"Cancel")
		->count();
		if ($checkorderindeal == 0) {
			$updateorderstatus  = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'order_cancelcomment' 	=> $request->order_cancelcomment,
				'orderstatus_id' 		=> $request->orderstatus_id,
			]); 
		}
		if($cancelorder){
		return response()->json(['message' => 'Order Cancelled Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function canceldeal(Request $request){
		$updateorderstatus;
		$validate = Validator::make($request->all(), [ 
	      'order_token'		=> 'required',
	      'orderstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$canceldeal  = DB::table('order')
			->where('order_token','=',$request->order_token)
			->update([
			'orderstatus_id' 		=> $request->orderstatus_id,
			'order_cancelcomment' 	=> $request->order_cancelcomment,
			'order_status' 			=> "Cancel",
		]); 
		if($canceldeal){
		return response()->json(['message' => 'Order Cancelled Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function savefollowup(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'followup_comment'	=> 'required',
	      'type'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields are Required", 400);
		}
		if ($request->type == "Merge") {
			$validate = Validator::make($request->all(), [ 
		      'mergedeal_token'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$gettoken = DB::table('mergedeal')
			->select('order_token')
			->where('mergedeal_token','=',$request->mergedeal_token)
			->where('created_by','=',$request->user_id)
			->where('status_id','=',1)
			->get();
			$getdealtoken = array();
			foreach ($gettoken as $gettokens) {
				$getdealtoken[] = $gettokens->order_token;
			}
			foreach ($getdealtoken as $getdealtokens) {
				$adds[] = array(
				'followup_comment' 	=> $request->followup_comment,
				'order_token' 		=> $getdealtokens,
				'status_id'		 	=> 1,
				'created_by'	 	=> $request->user_id,
				'created_at'	 	=> date('Y-m-d h:i:s'),
				);
			}
		    $save = DB::table('followup')->insert($adds);
		}else{
			$validate = Validator::make($request->all(), [ 
		      'order_token'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
				$adds[] = array(
				'followup_comment' 	=> $request->followup_comment,
				'order_token' 		=> $request->order_token,
				'status_id'		 	=> 1,
				'created_by'	 	=> $request->user_id,
				'created_at'	 	=> date('Y-m-d h:i:s'),
				);
			$save = DB::table('followup')->insert($adds);
		}
		if($save){
			return response()->json(['message' => 'Followup Saved Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function getfollowup(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'type'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->type == "Merge") {
			$validate = Validator::make($request->all(), [ 
		      'mergedeal_token'			=> 'required',
		    ]);
		 	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$getdeal = DB::table('mergedeal')
			->select('order_token')
			->where('mergedeal_token','=',$request->mergedeal_token)
			->where('status_id','=',1)
			->get();
			$getdealtoken = array();
			foreach ($getdeal as $getdeals) {
				$getdealtoken[] = $getdeals->order_token;
			}
			$getdealfollowup = DB::table('getdealfollowup')
				->select('*')
				->whereIn('order_token',$getdealtoken)
				->where('status_id','=',1)
				->groupBy('created_at')
				->get();
		}else{
			$validate = Validator::make($request->all(), [ 
		      'order_token'			=> 'required',
		    ]);
		 	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$getdealfollowup = DB::table('getdealfollowup')
				->select('*')
				->where('order_token','=',$request->order_token)
				->where('status_id','=',1)
				->get();
		}
		if($getdealfollowup){
		return response()->json(['data' => $getdealfollowup,'message' => 'Followup List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatepaypalinvoicenumber(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_paypalinvoicenumber'	=> 'required',
	      'type'						=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields are Required", 400);
		}
		if ($request->type == "Merge") {
			$validate = Validator::make($request->all(), [ 
		      'mergedeal_token'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$gettoken = DB::table('mergedeal')
			->select('order_token')
			->where('mergedeal_token','=',$request->mergedeal_token)
			->where('created_by','=',$request->user_id)
			->where('status_id','=',1)
			->get();
			$getdealtoken = array();
			foreach ($gettoken as $gettokens) {
				$getdealtoken[] = $gettokens->order_token;
			}
			$updatepaypal = DB::table('order')
				->whereIn('order_token',$getdealtoken)
				->update([
				'order_paypalinvoicenumber' => $request->order_paypalinvoicenumber,
				'updated_by'	 			=> $request->user_id,
				'updated_at'	 			=> date('Y-m-d h:i:s'),
			]);
		}else{
			$validate = Validator::make($request->all(), [ 
		      'order_token'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$updatepaypal = DB::table('order')
				->where('order_token','=',$request->order_token)
				->update([
				'order_paypalinvoicenumber' => $request->order_paypalinvoicenumber,
				'updated_by'	 			=> $request->user_id,
				'updated_at'	 			=> date('Y-m-d h:i:s'),
			]);
		}
		if($updatepaypal){
			return response()->json(['message' => 'Paypal Invoice Number Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function senddate(Request $request){
		$currentdate = date('Y-m-d');
		if($currentdate){
			return response()->json(['currentdate' => $currentdate],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function dealamount(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_token'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields are Required", 400);
		}
		if ($request->type == 12) {
		$getamount = DB::table('order')
		->select('order_amountquoted')
		->where('orderstatus_id','=',12)
		->where('order_token','=',$request->order_token)
		->sum('order_amountquoted');
		}else{
		$getamount = DB::table('order')
		->select('order_amountquoted')
		->where('orderstatus_id','!=',12)
		->where('order_token','=',$request->order_token)
		->sum('order_amountquoted');
		}
		if($getamount){
			return response()->json(['data' => $getamount],200);
		}else{
			return response()->json(['data' => "0"],200);
		}
	}
	public function mergedealamount(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'mergedeal_token'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields are Required", 400);
		}
		$getdeal = DB::table('mergedeal')
		->select('order_token')
		->where('mergedeal_token','=',$request->mergedeal_token)
		->where('status_id','=',1)
		->get();
		$getdealtoken = array();
		foreach ($getdeal as $getdeals) {
			$getdealtoken[] = $getdeals->order_token;
		}
		if ($request->type == 12) {
		$getamount = DB::table('order')
		->select('order_amountquoted')
		->whereIn('order_token',$getdealtoken)
		->where('orderstatus_id','=',12)
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}else{
		$getamount = DB::table('order')
		->select('order_amountquoted')
		->whereIn('order_token',$getdealtoken)
		->where('orderstatus_id','!=',12)
		->where('status_id','=',1)
		->sum('order_amountquoted');
		}
		if($getamount){
			return response()->json(['data' => $getamount],200);
		}else{
			return response()->json(['data' => "0"],200);
		}
	}
	public function unmergedeal(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'mergedeal_token'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$update = DB::table('mergedeal')
			->where('mergedeal_token','=',$request->mergedeal_token)
			->update([
			'status_id' 	=> 2,
		]);
		if($update){
			return response()->json(['message' => 'Un Merge Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
}