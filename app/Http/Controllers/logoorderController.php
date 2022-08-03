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

class logoorderController extends Controller
{
	public function createlogoorder(Request $request){
		// dd($request);
		$validate = Validator::make($request->all(), [ 
	    	'client_id'  						=> 'required',
		    'orderstatus_id' 					=> 'required',
		    'logoorder_amount'	 				=> 'required',
		    'logoorder_name'	 				=> 'required',
		    'logoorder_slogan'	 				=> 'required',
		    'logoorder_describebusiness'		=> 'required',
		    'logoorder_categorytype'	 		=> 'required',
		    'logoorder_colorrequirement'		=> 'required',
		    'logoorder_fontstyle'	 			=> 'required',
		    'logoorder_additionalinformation'	=> 'required',
		    'logocategory_id'					=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if (!empty($request->attachment)) {
		$validate = Validator::make($request->all(), [
	    	'attachment.*'=>'mimes:jpeg,bmp,png,jpg,ai,pdf,psd,eps,cdr,dst,emb,jdg,ofm,pxf,pes,JPEG,BMP,PNG,JPG,AI,PDF,PSD,EPS,CDR,DST,EMB,JDG,OFM,PXF,PES,doc,docx,heic',
	    		'attachment'  		=>'required',
	    		'attachment_type'  	=>'required',
    	]);
		if ($validate->fails()) {    
			return response()->json("Invalid Format", 400);
		}
		}
		$order_token = openssl_random_pseudo_bytes(7);
    	$order_token = bin2hex($order_token);
		// $order_token = mt_rand(100000, 999999);
		$attachment_token = $this->generateRandomString(100);
		$adds[] = array(
		'logoorder_amount' 					=> $request->logoorder_amount,
		'logoorder_name' 					=> $request->logoorder_name,
		'logoorder_slogan'					=> $request->logoorder_slogan,
		'logoorder_describebusiness' 		=> $request->logoorder_describebusiness,
		'logoorder_categorytype' 			=> $request->logoorder_categorytype,
		'logoorder_colorrequirement' 		=> $request->logoorder_colorrequirement,
		'logoorder_fontstyle'				=> $request->logoorder_fontstyle,
		'logoorder_additionalinformation'	=> $request->logoorder_additionalinformation,
		'logocategory_id' 					=> $request->logocategory_id,
		'attachment_token' 					=> $attachment_token,
		'logoorder_token'					=> $order_token,
		'logoorder_date'					=> date('Y-m-d'),
		'client_id' 						=> $request->client_id,
		'campaign_id' 						=> $request->campaign_id,
		'orderstatus_id'					=> $request->orderstatus_id,
		'status_id'		 					=> 1,
		'created_by'	 					=> $request->user_id,
		'created_at'	 					=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('logoorder')->insert($adds);
		$images = $request->attachment;
    	$index = 0 ;
    	$filename = array();
    	if ($request->has('attachment')) {
    		foreach($images as $ima){
    			$saveattachment = array();
        		if( $ima->isValid()){
        			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $attachment_token;
					$extension = $ima->getClientOriginalExtension();
		            $filename[$index] = $ima->getClientOriginalName();
		            $filename[$index] = $ima->move(public_path('logoorder/'.$foldername),$filename[$index]);
		            $filename[$index] = $ima->getClientOriginalName();
				  	$saveattachment[] = array(
					'attachment_name'		=> $filename[$index],
					'order_attachmenttoken'	=> $attachment_token,
					'attachment_type'		=> $request->attachment_type,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    	$index++;
        		}
        	DB::table('attachment')->insert($saveattachment);
        	}
        }
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
	public function logocategory(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'logoorder_categorytype'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getcategories = DB::table('logocategory')
		->select('*')
		->where('logoorder_categorytype','=',$request->logoorder_categorytype)
		->where('status_id','=',1)
		->get();
		if($getcategories){
			return response()->json(['data' => $getcategories,'message' => 'Logo Categories'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Category Not Found'],200);
		}
	}
	public function logoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('created_by','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerforwardedlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',2)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Forwarded Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerpicklogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',3)
		->where('logoorder_managerpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Pick Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	 public function unpicklogoorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'logoorder_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if($request->role_id == 2){
			$updateorderstatus = DB::table('logoorder')
				->where('logoorder_id','=',$request->logoorder_id)
				->update([
				'orderstatus_id'		=> 8,
				'logoorder_billingby'	=> null,
			]); 
		}
		if($request->role_id == 5){
			$updateorderstatus = DB::table('logoorder')
				->where('logoorder_id','=',$request->logoorder_id)
				->update([
				'orderstatus_id'		=> 4,
				'logoorder_workpickby'	=> null,
			]); 
		}
		if($request->role_id == 7){
			$updateorderstatus = DB::table('logoorder')
				->where('logoorder_id','=',$request->logoorder_id)
				->update([
				'orderstatus_id'		=> 2,
				'logoorder_managerpickby'	=> null,
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Unpick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function picklogoorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'logoorder_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if($request->role_id == 2){
			$updateorderstatus = DB::table('logoorder')
				->where('logoorder_id','=',$request->logoorder_id)
				->update([
				'orderstatus_id'		=> 9,
				'logoorder_billingby'	=> $request->user_id,
			]); 
		}
		if($request->role_id == 5){
			$updateorderstatus = DB::table('logoorder')
				->where('logoorder_id','=',$request->logoorder_id)
				->update([
				'orderstatus_id'		=> 16,
				'logoorder_workpickby'	=> $request->user_id,
			]); 
		}
		if($request->role_id == 7){
			$updateorderstatus = DB::table('logoorder')
				->where('logoorder_id','=',$request->logoorder_id)
				->update([
				'orderstatus_id'			=> 3,
				'logoorder_managerpickby'	=> $request->user_id,
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function logoorderdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'logoorder_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderdetails = DB::table('logoorderdetails')
		->select('*')
		->where('logoorder_id','=',$request->logoorder_id )
		->where('status_id','=',1)
		->first();

		$getorderattachmentclient = DB::table('attachment')
		->select('attachment_name','attachment_id')
		->where('order_attachmenttoken','=',$getorderdetails->attachment_token)
		->where('attachment_type','=',"clientlogo")
		->where('status_id','=',1)
		->get();
		$attachmentfiles = array();
		if (empty($getorderattachmentclient)) {
			$attachmentfiles= [];
		}else{
			$attachmentfiles = $getorderattachmentclient;
		}
		$getorderattachmentwork = DB::table('attachment')
		->select('attachment_name','attachment_id')
		->where('order_attachmenttoken','=',$getorderdetails->attachment_token)
		->where('attachment_type','=',"worklogo")
		->where('status_id','=',1)
		->get();
		$workattachmentfiles = array();
		if (empty($getorderattachmentwork)) {
			$workattachmentfiles= [];
		}else{
			$workattachmentfiles = $getorderattachmentwork;
		}
		if($getorderdetails){
			return response()->json(['data' => $getorderdetails,'attachmentdata' => $attachmentfiles, 'workattachmentdata' => $workattachmentfiles, 'message' => 'Order Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatelogoorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'orderstatus_id' 					=> 'required',
		    'logoorder_id'	 					=> 'required',
		    'logoorder_amount'	 				=> 'required',
		    'logoorder_name'	 				=> 'required',
		    'logoorder_slogan'	 				=> 'required',
		    'logoorder_describebusiness'		=> 'required',
		    'logoorder_categorytype'	 		=> 'required',
		    'logoorder_colorrequirement'		=> 'required',
		    'logoorder_fontstyle'	 			=> 'required',
		    'logoorder_additionalinformation'	=> 'required',
		    'logocategory_id'					=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if (!empty($request->attachment)) {
		$validate = Validator::make($request->all(), [
	    	'attachment.*'=>'mimes:jpeg,bmp,png,jpg,ai,pdf,psd,eps,cdr,dst,emb,jdg,ofm,pxf,pes,JPEG,BMP,PNG,JPG,AI,PDF,PSD,EPS,CDR,DST,EMB,JDG,OFM,PXF,PES,doc,docx,heic',
	    		'attachment'  		=>'required',
	    		'attachment_type'  	=>'required',
		    	'attachment_token'	=>'required',
    	]);
		if ($validate->fails()) {    
			return response()->json("Invalid Format", 400);
		}
		}
		 $save = DB::table('logoorder')
		->where('logoorder_id','=',$request->logoorder_id)
		->update([
		'logoorder_amount' 					=> $request->logoorder_amount,
		'logoorder_name' 					=> $request->logoorder_name,
		'logoorder_slogan'					=> $request->logoorder_slogan,
		'logoorder_describebusiness' 		=> $request->logoorder_describebusiness,
		'logoorder_categorytype' 			=> $request->logoorder_categorytype,
		'logoorder_colorrequirement' 		=> $request->logoorder_colorrequirement,
		'logoorder_fontstyle'				=> $request->logoorder_fontstyle,
		'logoorder_additionalinformation'	=> $request->logoorder_additionalinformation,
		'logocategory_id' 					=> $request->logocategory_id,
		'orderstatus_id'					=> $request->orderstatus_id,
		'updated_by'	 					=> $request->user_id,
		'updated_at'	 					=> date('Y-m-d h:i:s'),
		]);
		$images = $request->attachment;
    	$index = 0 ;
    	$filename = array();
    	if ($request->has('attachment')) {
    		foreach($images as $ima){
    			$saveattachment = array();
        		if( $ima->isValid()){
        			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $request->attachment_token;
					$extension = $ima->getClientOriginalExtension();
		            $filename[$index] = $ima->getClientOriginalName();
		            $filename[$index] = $ima->move(public_path('logoorder/'.$foldername),$filename[$index]);
		            $filename[$index] = $ima->getClientOriginalName();
				  	$saveattachment[] = array(
					'attachment_name'		=> $filename[$index],
					'order_attachmenttoken'	=> $request->attachment_token,
					'attachment_type'		=> $request->attachment_type,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    	$index++;
        		}
        	DB::table('attachment')->insert($saveattachment);
        	}
        }
		if($save){
			return response()->json(['message' => 'Order Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatelogoorderstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'logoorder_id'		=> 'required',
	      'orderstatus_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus  = DB::table('logoorder')
			->where('logoorder_id','=',$request->logoorder_id)
			->update([
			'orderstatus_id' 		=> $request->orderstatus_id,
		]); 
		if($updateorderstatus){
			return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function workerforwardedlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',4)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Forwarded Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function workerpicklogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',16)
		->where('logoorder_workpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Pick Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function workerlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('logoorder_workpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function submitlogoorderwork(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'logoorder_id'  	=> 'required',
		    'orderstatus_id' 	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if (!empty($request->attachment)) {
		$validate = Validator::make($request->all(), [
	    	'attachment.*'=>'mimes:jpeg,bmp,png,jpg,ai,pdf,psd,eps,cdr,dst,emb,jdg,ofm,pxf,pes,JPEG,BMP,PNG,JPG,AI,PDF,PSD,EPS,CDR,DST,EMB,JDG,OFM,PXF,PES,doc,docx,heic',
	    		'attachment'  		=>'required',
	    		'attachment_type'  	=>'required',
	    		'attachment_token'	=> 'required',
    	]);
		if ($validate->fails()) {    
			return response()->json("Invalid Format", 400);
		}
		}
		 $save = DB::table('logoorder')
		->where('logoorder_id','=',$request->logoorder_id)
		->update([
		'orderstatus_id'	=> $request->orderstatus_id,
		'updated_by'	 	=> $request->user_id,
		'updated_at'	 	=> date('Y-m-d h:i:s'),
		]);
		$images = $request->attachment;
    	$index = 0 ;
    	$filename = array();
    	if ($request->has('attachment')) {
    		foreach($images as $ima){
    			$saveattachment = array();
        		if( $ima->isValid()){
        			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $request->attachment_token;
					$extension = $ima->getClientOriginalExtension();
		            $filename[$index] = $ima->getClientOriginalName();
		            $filename[$index] = $ima->move(public_path('worklogoorder/'.$foldername),$filename[$index]);
		            $filename[$index] = $ima->getClientOriginalName();
				  	$saveattachment[] = array(
					'attachment_name'		=> $filename[$index],
					'order_attachmenttoken'	=> $request->attachment_token,
					'attachment_type'		=> $request->attachment_type,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    	$index++;
        		}
        	DB::table('attachment')->insert($saveattachment);
        	}
        }
		if($save){
			return response()->json(['message' => 'Logo Work Submited Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function managerforwardedunpaidlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('logoorder_managerpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerunpaidlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('created_by','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerforwardedpaidlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->whereIn('orderstatus_id',[11,18])
		->where('logoorder_managerpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerpaidlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->whereIn('orderstatus_id',[11,18])
		->where('created_by','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerforwardedcancellogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('logoorder_managerpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managercancellogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('created_by','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	////
	public function billingforwardedlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',8)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		$emptyarray = array();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Forwarded Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingpicklogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}if ($request->role_id == 2) {
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',9)
		->where('logoorder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',9)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}
		$emptyarray = array();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Pick Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingunpaidlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=', 10)
		->where('logoorder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=', 10)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingpaidlogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',11)
		->where('logoorder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',11)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingrecoverylogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',18)
		->where('logoorder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',18)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingcancellogoorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('logoorder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();	
		}else{
		$getorderlist = DB::table('logoorderlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('logoorder_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingsentlogoordreinvoice(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'logoorder_id'						=> 'required',
	      'orderstatus_id'					=> 'required',
	      'logoorder_paypalinvoicenumber'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus  = DB::table('logoorder')
			->where('logoorder_id','=',$request->logoorder_id)
			->update([
			'orderstatus_id' 				=> $request->orderstatus_id,
			'logoorder_paypalinvoicenumber' => $request->logoorder_paypalinvoicenumber,
		]); 
		if($updateorderstatus){
		return response()->json(['message' => 'Invoice Sent Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatelogopaypalinvoicenumber(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'logoorder_paypalinvoicenumber'	=> 'required',
	      'logoorder_id'					=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields are Required", 400);
		}
		$validate = Validator::make($request->all(), [ 
	    ]);
     	$updatepaypal = DB::table('logoorder')
			->where('logoorder_id','=',$request->logoorder_id)
			->update([
			'logoorder_paypalinvoicenumber' 	=> $request->logoorder_paypalinvoicenumber,
			'updated_by'	 					=> $request->user_id,
			'updated_at'	 					=> date('Y-m-d h:i:s'),
		]);
		if($updatepaypal){
			return response()->json(['message' => 'Paypal Invoice Number Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
}