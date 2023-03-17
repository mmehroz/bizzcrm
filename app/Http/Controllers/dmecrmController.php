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
use DateTime;

class dmecrmController extends Controller
{
	public function createdmeoorder(Request $request){
		$wdate = date('Y-m-d');
		$weekdate = new DateTime($wdate);
		$weeknumber = $weekdate->format("W");
		$validate = Validator::make($request->all(), [ 
		    'orderstatus_id' 						=> 'required',
		    'dmeclient_homephone'	 				=> 'required',
		    'dmeinsurance_formid'	 				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if (empty($request->created_at)) {
		$validate = Validator::make($request->all(), [ 
		    'dmeclient_homephone' 		=> 'unique:dmeclient,dmeclient_homephone',
		    'dmeinsurance_formid' 		=> 'unique:dmeinsurance,dmeinsurance_formid',
		]);
		if ($validate->fails()) {    
			return response()->json("Already Exist", 400);
		}
		$dmeattachment = array();
		if (!empty($request->dmeotherdetails_attachment)) {
			$ima = $request->dmeotherdetails_attachment;
			$index=0;
			foreach ($ima as $imas) {
			if($imas->isValid()){
	            $number = rand(1,999);
		        $numb = $number / 7 ;
				$name = "dmeattachment";
		        $extension = $imas->extension();
	            $dmeattachment[$index] = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	            $dmeattachment[$index] = $imas->move(public_path('dmeattachment/'),$dmeattachment[$index]);
			    $dmeattachment[$index] = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	        	$index++;
	        }
			}
		$mergeattachmentname = implode(",", $dmeattachment);
        }else{
        	$mergeattachmentname = null;
        }
    	}else{
    	if (!empty($request->dmeotherdetails_attachment)) {
			$ima = $request->dmeotherdetails_attachment;
			$index=0;
			foreach ($ima as $imas) {
			if($imas->isValid()){
	            $number = rand(1,999);
		        $numb = $number / 7 ;
				$name = "dmeattachment";
		        $extension = $imas->extension();
	            $dmeattachment[$index] = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	            $dmeattachment[$index] = $imas->move(public_path('dmeattachment/'),$dmeattachment[$index]);
			    $dmeattachment[$index] = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	        	$index++;
	        }
			}
			if (!empty($request->dmeoldattachment)) {
    		$oldname = explode(',', $request->dmeoldattachment);
    		$mergename = array_merge($dmeattachment,$oldname);
    		$mergeattachmentname = implode(',', $mergename);
    		}else{
    		$mergeattachmentname = implode(',', $dmeattachment);
    		}
        }else{
        	if (!empty($request->dmeoldattachment)) {
        	$mergeattachmentname = $request->dmeoldattachment;
        	}else{
    		$mergeattachmentname = null;
    		}
        }	
    	}
    	$order_token = openssl_random_pseudo_bytes(7);
		$order_token = bin2hex($order_token);
		// $order_token = mt_rand(100000, 999999);
		$adds[] = array(
		'dmeclient_name' 			=> $request->dmeclient_name,
		'dmeclient_lastname' 		=> "-",
		'dmeclient_email'			=> $request->dmeclient_email,
		'dmeclient_dateofbirth' 	=> $request->dmeclient_dateofbirth,
		'dmeclient_homephone' 		=> $request->dmeclient_homephone,
		'dmeclient_cellphone' 		=> $request->dmeclient_cellphone,
		'dmeclient_bestcalltime'	=> $request->dmeclient_bestcalltime,
		'dmeclient_videochataccess'	=> $request->dmeclient_videochataccess,
		'dmeclient_smartphone'		=> $request->dmeclient_smartphone,
		'dmeclient_rateyourpain'	=> $request->dmeclient_rateyourpain,
		'dmeclient_gender' 			=> $request->dmeclient_gender,
		'dmeclient_address' 		=> $request->dmeclient_address,
		'dmeclient_city' 			=> $request->dmeclient_city,
		'dmeclient_state' 			=> $request->dmeclient_state,
		'dmeclient_zip'				=> $request->dmeclient_zip,
		'dmeclient_heightfeet' 		=> $request->dmeclient_heightfeet,
		'dmeclient_heightinches' 	=> $request->dmeclient_heightinches,
		'dmeclient_weight' 			=> $request->dmeclient_weight,
		'dmeclient_waist'			=> $request->dmeclient_waist,
		'dmeclient_shoesize'		=> $request->dmeclient_shoesize,
		'dmeclient_agentreson'		=> $request->dmeclient_agentreson,
		'dmeclient_managerreson'	=> $request->dmeclient_managerreson,
		'dmeclient_cardtype'		=> $request->dmeclient_cardtype,
		'dmeclient_paincause'		=> $request->dmeclient_paincause,
		'dmeclient_medication'		=> $request->dmeclient_medication,
		'dmeclient_ethnicity'		=> $request->dmeclient_ethnicity,
		'dmeservices_id' 			=> $request->dmeservices_id,
		'dmeclient_teamleadid' 		=> $request->dmeclient_teamleadid,
		'dmeclient_islivetransfer' 	=> $request->dmeclient_islivetransfer,
		'dmeclient_token'			=> $order_token,
		'dmeclient_year'			=> date('Y'),
		'dmeclient_week'			=> $weeknumber,
		'dmeclient_date'			=> date('Y-m-d'),
		'campaign_id' 				=> $request->campaign_id,
		'orderstatus_id'			=> $request->orderstatus_id,
		'status_id'		 			=> 1,
		'created_by'	 			=> $request->user_id,
		'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('dmeclient')->insert($adds);
		$dmeclient_id = DB::getPdo()->lastInsertId();
		if (!empty($request->created_at)) {
			DB::table('dmeclient')
			->where('dmeclient_id','=',$dmeclient_id)
			->update([
			'dmeclient_billingby'		=> $request->user_id,
			'dmeclient_chaseby'			=> $request->user_id,
			'dmeclient_isnonprocess'	=> 1,
			]);	
		}
		$addothers[] = array(
		'dmeinsurance_insurance' 				=> $request->dmeinsurance_insurance,
		'dmeinsurance_formid' 					=> $request->dmeinsurance_formid,
		'dmeinsurance_secondarytype'			=> $request->dmeinsurance_secondarytype,
		'dmeinsurance_secondaryname' 			=> $request->dmeinsurance_secondaryname,
		'dmeinsurance_secondarypolicynumber' 	=> $request->dmeinsurance_secondarypolicynumber,
		'dmeinsurance_diabetic' 				=> $request->dmeinsurance_diabetic,
		'dmeinsurance_insulin'					=> $request->dmeinsurance_insulin,
		'dmeinsurance_timestest'				=> $request->dmeinsurance_timestest,
		'dmeinsurance_timesinject' 				=> $request->dmeinsurance_timesinject,
		'dmeinsurance_painlocation' 			=> $request->dmeinsurance_painlocation,
		'dmeinsurance_diabeticother' 			=> $request->dmeinsurance_diabeticother,
		'dmeclient_id' 							=> $dmeclient_id,
		'dmeclient_token' 						=> $order_token,
		'status_id'		 						=> 1,
		'created_by'	 						=> $request->user_id,
		'created_at'	 						=> date('Y-m-d h:i:s'),
		);
		$saveothers = DB::table('dmeinsurance')->insert($addothers);
		$addmoreothers[] = array(
		'dmeotherdetails_cardiotype' 		=> $request->dmeotherdetails_cardiotype,
		'dmeotherdetails_hearbeat' 			=> $request->dmeotherdetails_hearbeat,
		'dmeotherdetails_levac'				=> $request->dmeotherdetails_levac,
		'dmeotherdetails_stunt' 			=> $request->dmeotherdetails_stunt,
		'dmeotherdetails_heartattack' 		=> $request->dmeotherdetails_heartattack,
		'dmeotherdetails_inlargeheart' 		=> $request->dmeotherdetails_inlargeheart,
		'dmeotherdetails_memer'				=> $request->dmeotherdetails_memer,
		'dmeotherdetails_highcorostol'		=> $request->dmeotherdetails_highcorostol,
		'dmeotherdetails_highlowbp' 		=> $request->dmeotherdetails_highlowbp,
		'dmeotherdetails_dropbp' 			=> $request->dmeotherdetails_dropbp,
		'dmeotherdetails_height' 			=> $request->dmeotherdetails_height,
		'dmeotherdetails_weight' 			=> $request->dmeotherdetails_weight,
		'dmeotherdetails_waist'				=> $request->dmeotherdetails_waist,
		'dmeotherdetails_shoesize' 			=> $request->dmeotherdetails_shoesize,
		'dmeotherdetails_qualifying' 		=> $request->dmeotherdetails_qualifying,
		'dmeotherdetails_uvward' 			=> $request->dmeotherdetails_uvward,
		'dmeotherdetails_allskinissue'		=> $request->dmeotherdetails_allskinissue,
		'dmeotherdetails_skinallergy'		=> $request->dmeotherdetails_skinallergy,
		'dmeotherdetails_dryexzima' 		=> $request->dmeotherdetails_dryexzima,
		'dmeotherdetails_mustskinissue' 	=> $request->dmeotherdetails_mustskinissue,
		'dmeotherdetails_drname'			=> $request->dmeotherdetails_drname,
		'dmeotherdetails_drphone' 			=> $request->dmeotherdetails_drphone,
		'dmeotherdetails_draddress' 		=> $request->dmeotherdetails_draddress,
		'dmeotherdetails_cancerother' 		=> $request->dmeotherdetails_cancerother,
		'dmeotherdetails_hascancer' 		=> $request->dmeotherdetails_hascancer,
		'dmeotherdetails_drnpi' 			=> $request->dmeotherdetails_drnpi,
		'dmeotherdetails_drfaxnumber' 		=> $request->dmeotherdetails_drfaxnumber,
		'dmeotherdetails_attachment' 		=> $mergeattachmentname,
		'dmeotherdetails_merchant' 			=> $request->dmeotherdetails_merchant,
		'dmeclient_id' 						=> $dmeclient_id,
		'dmeclient_token' 					=> $order_token,
		'status_id'		 					=> 1,
		'created_by'	 					=> $request->user_id,
		'created_at'	 					=> date('Y-m-d h:i:s'),
		);
		$savemoreothers = DB::table('dmeotherdetails')->insert($addmoreothers);
		if($save){
			return response()->json(['order_token' => $order_token, 'message' => 'Order Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmeorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','!=',1)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('created_by','=',$request->user_id)
		->where('orderstatus_id','!=',1)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmesaveorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',1)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('created_by','=',$request->user_id)
		->where('orderstatus_id','=',1)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagerforwardedorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		// ->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',2)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagerpickorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',3)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->where('orderstatus_id','=',3)
		->where('dmeclient_managerpickby','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagerforwardedagentorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->whereNotIn('orderstatus_id', [2,3])
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->whereNotIn('orderstatus_id', [2,3])
		->where('dmeclient_managerpickby','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagerforwardedcancelorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('dmeclient_managerpickby','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagercancelorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->where('created_by','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagerforwardedsubmitedorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',20)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',20)
		->where('dmeclient_managerpickby','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagersubmitedorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',20)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',20)
		->where('created_by','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagerforwardedbillingorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',8)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',8)
		->where('dmeclient_managerpickby','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagerbillingorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',8)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',8)
		->where('created_by','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagerforwardedapproveorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',19)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',19)
		->where('dmeclient_managerpickby','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmemanagerapproveorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	      'from'			=> 'required',
	      'to'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$from = $request->from;
		$to = $request->to;
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_billinglastupdateddate', [$from, $to])
		->whereIn('orderstatus_id',[8,28,29,30,31,32,33,34])
		->where('dmeclient_isprocess','=',1)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereBetween('dmeclient_billinglastupdateddate', [$from, $to])
		->whereIn('orderstatus_id',[8,28,29,30,31,32,33,34])
		->where('dmeclient_isprocess','=',1)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	 public function unpickdmeorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'dmeclient_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if($request->role_id == 2){
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billingby'				=> null,
				'dmeclient_billingunpickcomment'	=> $request->dmeclient_billingunpickcomment,
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		if($request->role_id == 3){
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'orderstatus_id'					=> 2,
				'dmeclient_managerpickby'			=> null,
				'dmeclient_managerlastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		if($request->role_id == 10){
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_chaseby'					=> null,
				'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Unpick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function pickdmeorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'dmeclient_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		$ispick = DB::table('dmeorderdetails')
		->select('dmeclient_billingby','dmeclient_chaseby')
		->where('status_id','=',1)
		->where('dmeclient_id','=',$request->dmeclient_id)
		->first();
		if($request->role_id == 2){
			if ($ispick->dmeclient_billingby != NULL) {
				return response()->json("Already Picked", 400);
			}else{
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billingby'				=> $request->user_id,
				'dmeclient_isreturnfrombilling'		=> 0,
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
			]); 
			}
		}
		if($request->role_id == 3){
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'orderstatus_id'					=> 3,
				'dmeclient_managerpickby'			=> $request->user_id,
				'dmeclient_managerlastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		if($request->role_id == 10){
			if ($ispick->dmeclient_chaseby != NULL) {
				return response()->json("Already Picked", 400);
			}else{
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_chaseby'					=> $request->user_id,
				'dmeclient_isreturnfromchase'		=> 0,
				'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
			]); 
			}
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmebillingforwardedorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('dmeclient_cardtype','=',"Medicare part B active")
		->whereBetween('dmeclient_date', [$from, $to])
		->where('dmeclient_billingby','=',null)
		->where('orderstatus_id','=',8)
		->orderBy('dmeclient_id','DESC')
		->get();
		// dd($getorderlist);
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmebillingpickorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('dmeclient_cardtype','=',"Medicare part B active")
		->where('dmeclient_billingby','=',$request->user_id)
		->where('orderstatus_id','=',8)
		->where('dmeclient_isfilled','=',0)
		->where('dmeclient_isarchived','=',0)
		->where('dmeclient_isprocess','=',0)
		->where('dmeclient_isnonprocess','=',0)
		->where('dmeclient_isreturnfrombilling','=',0)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function updatedmeorderstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmeclient_id'			=> 'required',
	      'orderstatus_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if ($request->orderstatus_id == 9) {
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 		=> $request->orderstatus_id,
			]); 
			$getorderdetailtopost = DB::table('dmeorderdetails')
			->select('*')
			->where('status_id','=',1)
			->where('dmeclient_id','=',$request->dmeclient_id)
			->first();
				// $post = [
			// 'affiliate_agent_name' 				=> $getorderdetailtopost->user_name,
			// 'first_name' 						=> $getorderdetailtopost->dmeclient_name,
			// 'last_name' 						=> $getorderdetailtopost->dmeclient_lastname,
			// 'email_address'						=> $getorderdetailtopost->dmeclient_email,
			// 'dob' 								=> $getorderdetailtopost->dmeclient_dateofbirth,
			// 'phone_home' 						=> $getorderdetailtopost->dmeclient_homephone,
			// 'phone_cell' 						=> $getorderdetailtopost->dmeclient_cellphone,
			// 'best_time_to_call'					=> $getorderdetailtopost->dmeclient_bestcalltime,
			// 'telemedicine'						=> $getorderdetailtopost->dmeclient_videochataccess,
			// 'gender' 							=> $getorderdetailtopost->dmeclient_gender,
			// 'address' 							=> $getorderdetailtopost->dmeclient_address,
			// 'city' 								=> $getorderdetailtopost->dmeclient_city,
			// 'state' 							=> $getorderdetailtopost->dmeclient_state,
			// 'zip_code'							=> $getorderdetailtopost->dmeclient_zip,
			// 'height_feet' 						=> $getorderdetailtopost->dmeclient_heightfeet,
			// 'height_inches' 					=> $getorderdetailtopost->dmeclient_heightinches,
			// 'weight' 							=> $getorderdetailtopost->dmeclient_weight,
			// 'waist_size'						=> $getorderdetailtopost->dmeclient_waist,
			// 'shoe_size'							=> $getorderdetailtopost->dmeclient_shoesize,
			// 'primary_insurance_name' 			=> $getorderdetailtopost->dmeinsurance_insurance,
			// 'primary_insurance_policy_number' 	=> $getorderdetailtopost->dmeinsurance_formid,
			// 'secondary_insurance'				=> $getorderdetailtopost->dmeinsurance_secondarytype,
			// 'secondary_insurance_name' 			=> $getorderdetailtopost->dmeinsurance_secondaryname,
			// 'secondary_insurance_policy_number' => $getorderdetailtopost->dmeinsurance_secondarypolicynumber,
			// 'diabetic' 							=> $getorderdetailtopost->dmeinsurance_diabetic,
			// 'diabetic_insulin'					=> $getorderdetailtopost->dmeinsurance_insulin,
			// 'diabetic_tests'					=> $getorderdetailtopost->dmeinsurance_timestest,
			// 'diabetic_injections' 				=> $getorderdetailtopost->dmeinsurance_timesinject,
			// 'pain_location' 					=> $getorderdetailtopost->dmeinsurance_painlocation,
			// ];
			// $ch = curl_init('https://trueprospects.leadspediatrack.com/post.do?lp_campaign_key=hbyHWdXrpCDqLGZYVfgQ&lp_campaign_id=61265a2bbcc7b');
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			// $response = curl_exec($ch);
			// curl_close($ch);
		}elseif($request->orderstatus_id == 6) {
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 			=> $request->orderstatus_id,
			'dmeclient_managerreson' 	=> $request->dmeclient_managerreson,
			'dmeclient_cardtype' 		=> $request->dmeclient_cardtype,
			]);
		}elseif($request->orderstatus_id == 25) {
			if ($request->role_id == 2) {
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 			=> $request->orderstatus_id,
			]);
			DB::table('dmeotherdetails')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'dmeotherdetails_billingcomment' 	=> $request->dmeotherdetails_billingcomment,
			]);	
			}else{
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 			=> $request->orderstatus_id,
			]);
			DB::table('dmeotherdetails')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'dmeotherdetails_chasecomment' 	=> $request->dmeotherdetails_chasecomment,
			]);
			}
		}elseif($request->orderstatus_id == 12) {
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 			=> $request->orderstatus_id,
			]);
			if (!empty($dmeotherdetails_billingcomment)) {
				DB::table('dmeotherdetails')
				->where('dmeclient_id','=',$request->dmeclient_id )
				->update([
				'dmeotherdetails_billingcomment' 	=> $request->dmeotherdetails_billingcomment,
				]);
			}if (!empty($dmeotherdetails_chasecomment)) {
				DB::table('dmeotherdetails')
				->where('dmeclient_id','=',$request->dmeclient_id )
				->update([
				'dmeotherdetails_chasecomment' 	=> $request->dmeotherdetails_chasecomment,
				]);
			}
		}elseif($request->orderstatus_id == 26) {
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 					=> $request->orderstatus_id,
			]);
		}elseif($request->orderstatus_id == 30) {
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 			=> $request->orderstatus_id,
			]);
			$updateorderstatus  = DB::table('dmeotherdetails')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'dmeotherdetails_admincomment' 	=> $request->dmeotherdetails_admincomment,
			]);
		}elseif($request->orderstatus_id == 34) {
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 			=> $request->orderstatus_id,
			]);
			$updateorderstatus  = DB::table('dmeotherdetails')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'dmeotherdetails_admincomment' 	=> $request->dmeotherdetails_admincomment,
			]);
		}
		elseif($request->orderstatus_id == 31) {
			$getmerchantid = DB::table('dmeclient')
			->select('dmemerchant_id','dmeclient_year','dmeclient_week')
			->where('dmeclient_id','=',$request->dmeclient_id)
			->where('status_id','=',1)
			->first();
			$getmerchantdetail = DB::table('dmemerchant')
			->select('dmemerchant_rate','dmemerchant_type')
			->where('dmemerchant_id','=',$getmerchantid->dmemerchant_id)
			->where('status_id','=',1)
			->first();
			if (isset($getmerchantdetail->dmemerchant_type)) {
			if ($getmerchantdetail->dmemerchant_type == "Daily") {
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'dmeclient_amount' 			=> $getmerchantdetail->dmemerchant_rate,
			'orderstatus_id' 			=> $request->orderstatus_id,
			]);
			}else{
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 			=> $request->orderstatus_id,
			]);	
			}
			}else{
			if (isset($getmerchantdetail->dmemerchant_rate)) {
			$getweeklydeals = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('dmeclient_year','=',$getmerchantid->dmeclient_year)
			->where('dmeclient_week','=',$getmerchantid->dmeclient_week)
			->where('status_id','=',1)
			->count();
			$dealrate = $getmerchantdetail->dmemerchant_rate/$getweeklydeals;
			$getweeklydealid = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('dmeclient_year','=',$getmerchantid->dmeclient_year)
			->where('dmeclient_week','=',$getmerchantid->dmeclient_week)
			->where('status_id','=',1)
			->get();
			if (isset($getweeklydealid)) {
			$sortweelydealid = array();
			foreach ($getweeklydealid as $getweeklydealids) {
				$sortweelydealid[] = $getweeklydealids->dmeclient_id;
			}
			foreach ($sortweelydealid as $sortweelydealids) {
				DB::table('dmeclient')
				->where('dmeclient_id','=',$sortweelydealids )
				->update([
				'dmeclient_amount' 			=> $dealrate,
				]);
			}
			}
			}else{
			$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 			=> $request->orderstatus_id,
			]);
			}
			}
		}
		else{
		$updateorderstatus  = DB::table('dmeclient')
			->where('dmeclient_id','=',$request->dmeclient_id )
			->update([
			'orderstatus_id' 		=> $request->orderstatus_id,
		]); 
		}
		if($request->role_id == 2){
			 DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
			]); 
		}if($request->role_id == 3){
			 DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_managerlastupdateddate'	=> date('Y-m-d'),
			]); 
		}if($request->role_id == 10){
			 DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		if($updateorderstatus){
			return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmepaidorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',11)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmecancelorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',12)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmeapprovedorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('dmeclient_billingby','=',$request->user_id)
		->where('orderstatus_id','=',19)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmeorderdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmeclient_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderdetails = DB::table('dmeorderdetails')
		->select('*')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->where('status_id','=',1)
		->first();
		$getotherorderdetails = DB::table('dmeotherdetails')
		->select('*')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->where('status_id','=',1)
		->first();
		if($getorderdetails){
			return response()->json(['data' => $getorderdetails, 'otherdata' => $getotherorderdetails, 'message' => 'Order Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatedmeoorder(Request $request){
		$validate = Validator::make($request->all(), [ 
		    'dmeclient_id'							=> 'required',
		    'dmeclient_token'						=> 'required',
		    'orderstatus_id' 						=> 'required',
		    'dmeclient_name'	 					=> 'required',
		    'dmeclient_email'	 					=> 'required',
		    'dmeclient_dateofbirth'					=> 'required',
		    'dmeclient_homephone'	 				=> 'required',
		    'dmeclient_cellphone'					=> 'required',
		    'dmeclient_bestcalltime'				=> 'required',
		    'dmeclient_videochataccess'				=> 'required',
		    'dmeclient_smartphone'					=> 'required',
		    'dmeclient_rateyourpain'				=> 'required',
	    	'dmeclient_gender'  					=> 'required',
			'dmeclient_address'	 					=> 'required',
		    'dmeclient_city'	 					=> 'required',
		    'dmeclient_state'	 					=> 'required',
		    'dmeclient_zip'							=> 'required',
		    'dmeclient_heightfeet'	 				=> 'required',
		    'dmeclient_heightinches'				=> 'required',
		    'dmeclient_weight'	 					=> 'required',
		    'dmeclient_waist'						=> 'required',
	    	'dmeclient_shoesize'  					=> 'required',
	    	'dmeclient_agentreson'  				=> 'required',
	    	'dmeclient_managerreson'  				=> 'required',
			'dmeclient_cardtype'  					=> 'required',
	    	'dmeclient_paincause'  					=> 'required',
	    	'dmeclient_medication'  				=> 'required',
	    	'dmeclient_ethnicity'  					=> 'required',
	    	'dmeservices_id'  						=> 'required',
			'dmeinsurance_insurance'  				=> 'required',
		    'dmeinsurance_formid'					=> 'required',
		    'dmeinsurance_secondarytype'	 		=> 'required',
		    'dmeinsurance_secondaryname'			=> 'required',
		    'dmeinsurance_secondarypolicynumber'	=> 'required',
		    'dmeinsurance_diabetic'					=> 'required',
	    	'dmeinsurance_insulin'  				=> 'required',
	    	'dmeinsurance_timestest'  				=> 'required',
		    'dmeinsurance_timesinject'				=> 'required',
		    'dmeinsurance_painlocation'				=> 'required',
		    'dmeinsurance_diabeticother'			=> 'required',
			'dmeotherdetails_cardiotype'			=> 'required',
		    'dmeotherdetails_hearbeat'				=> 'required',
	    	'dmeotherdetails_levac'  				=> 'required',
			'dmeotherdetails_stunt'	 				=> 'required',
		    'dmeotherdetails_heartattack'	 		=> 'required',
		    'dmeotherdetails_inlargeheart'	 		=> 'required',
		    'dmeotherdetails_memer'					=> 'required',
		    'dmeotherdetails_highcorostol'	 		=> 'required',
		    'dmeotherdetails_highlowbp'				=> 'required',
		    'dmeotherdetails_dropbp'	 			=> 'required',
		    'dmeotherdetails_height'				=> 'required',
	    	'dmeotherdetails_weight'  				=> 'required',
	    	'dmeotherdetails_waist'  				=> 'required',
		    'dmeotherdetails_shoesize'				=> 'required',
		    'dmeotherdetails_qualifying'	 		=> 'required',
		    'dmeotherdetails_uvward'				=> 'required',
		    'dmeotherdetails_allskinissue'			=> 'required',
		    'dmeotherdetails_skinallergy'			=> 'required',
	    	'dmeotherdetails_dryexzima'  			=> 'required',
	    	'dmeotherdetails_mustskinissue'  		=> 'required',
	    	'dmeotherdetails_drname'				=> 'required',
			'dmeotherdetails_drphone'				=> 'required',
			'dmeotherdetails_draddress'				=> 'required',
			'dmeotherdetails_cancerother'			=> 'required',
			'dmeotherdetails_hascancer'				=> 'required',
			'dmeotherdetails_drnpi'					=> 'required',
			'dmeotherdetails_drfaxnumber'			=> 'required',
			'dmeotherdetails_merchant'				=> 'required',
			'dmeotherdetails_comment'				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if($request->role_id == 4){
		$save = DB::table('dmeclient')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->update([
		'dmeclient_name' 			=> $request->dmeclient_name,
		'dmeclient_lastname' 		=> "-",
		'dmeclient_email'			=> $request->dmeclient_email,
		'dmeclient_dateofbirth' 	=> $request->dmeclient_dateofbirth,
		'dmeclient_homephone' 		=> $request->dmeclient_homephone,
		'dmeclient_cellphone' 		=> $request->dmeclient_cellphone,
		'dmeclient_bestcalltime'	=> $request->dmeclient_bestcalltime,
		'dmeclient_videochataccess'	=> $request->dmeclient_videochataccess,
		'dmeclient_smartphone'		=> $request->dmeclient_smartphone,
		'dmeclient_rateyourpain'	=> $request->dmeclient_rateyourpain,
		'dmeclient_gender' 			=> $request->dmeclient_gender,
		'dmeclient_address' 		=> $request->dmeclient_address,
		'dmeclient_city' 			=> $request->dmeclient_city,
		'dmeclient_state' 			=> $request->dmeclient_state,
		'dmeclient_zip'				=> $request->dmeclient_zip,
		'dmeclient_heightfeet' 		=> $request->dmeclient_heightfeet,
		'dmeclient_heightinches' 	=> $request->dmeclient_heightinches,
		'dmeclient_weight' 			=> $request->dmeclient_weight,
		'dmeclient_waist'			=> $request->dmeclient_waist,
		'dmeclient_shoesize'		=> $request->dmeclient_shoesize,
		'dmeclient_agentreson'		=> $request->dmeclient_agentreson,
		'dmeclient_managerreson'	=> $request->dmeclient_managerreson,
		'dmeclient_cardtype'		=> $request->dmeclient_cardtype,
		'dmeclient_paincause'		=> $request->dmeclient_paincause,
		'dmeclient_medication'		=> $request->dmeclient_medication,
		'dmeclient_ethnicity'		=> $request->dmeclient_ethnicity,
		'dmeservices_id'			=> $request->dmeservices_id,
		'dmeclient_islivetransfer' 	=> $request->dmeclient_islivetransfer,
		'orderstatus_id'			=> $request->orderstatus_id,
		'updated_by'	 			=> $request->user_id,
		'updated_at'	 			=> date('Y-m-d h:i:s'),
		]);
		}else{
		$save = DB::table('dmeclient')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->update([
		'dmeclient_name' 			=> $request->dmeclient_name,
		'dmeclient_lastname' 		=> "-",
		'dmeclient_email'			=> $request->dmeclient_email,
		'dmeclient_dateofbirth' 	=> $request->dmeclient_dateofbirth,
		'dmeclient_homephone' 		=> $request->dmeclient_homephone,
		'dmeclient_cellphone' 		=> $request->dmeclient_cellphone,
		'dmeclient_bestcalltime'	=> $request->dmeclient_bestcalltime,
		'dmeclient_videochataccess'	=> $request->dmeclient_videochataccess,
		'dmeclient_smartphone'		=> $request->dmeclient_smartphone,
		'dmeclient_rateyourpain'	=> $request->dmeclient_rateyourpain,
		'dmeclient_gender' 			=> $request->dmeclient_gender,
		'dmeclient_address' 		=> $request->dmeclient_address,
		'dmeclient_city' 			=> $request->dmeclient_city,
		'dmeclient_state' 			=> $request->dmeclient_state,
		'dmeclient_zip'				=> $request->dmeclient_zip,
		'dmeclient_heightfeet' 		=> $request->dmeclient_heightfeet,
		'dmeclient_heightinches' 	=> $request->dmeclient_heightinches,
		'dmeclient_weight' 			=> $request->dmeclient_weight,
		'dmeclient_waist'			=> $request->dmeclient_waist,
		'dmeclient_shoesize'		=> $request->dmeclient_shoesize,
		'dmeclient_managerreson'	=> $request->dmeclient_managerreson,
		'dmeclient_cardtype'		=> $request->dmeclient_cardtype,
		'dmeclient_paincause'		=> $request->dmeclient_paincause,
		'dmeclient_medication'		=> $request->dmeclient_medication,
		'dmeclient_ethnicity'		=> $request->dmeclient_ethnicity,
		'dmeservices_id'			=> $request->dmeservices_id,
		'dmeclient_islivetransfer' 	=> $request->dmeclient_islivetransfer,
		'orderstatus_id'			=> $request->orderstatus_id,
		'updated_by'	 			=> $request->user_id,
		'updated_at'	 			=> date('Y-m-d h:i:s'),
		]);
		}
		DB::table('dmeinsurance')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->update([
		'dmeinsurance_insurance' 				=> $request->dmeinsurance_insurance,
		'dmeinsurance_formid' 					=> $request->dmeinsurance_formid,
		'dmeinsurance_secondarytype'			=> $request->dmeinsurance_secondarytype,
		'dmeinsurance_secondaryname' 			=> $request->dmeinsurance_secondaryname,
		'dmeinsurance_secondarypolicynumber' 	=> $request->dmeinsurance_secondarypolicynumber,
		'dmeinsurance_diabetic' 				=> $request->dmeinsurance_diabetic,
		'dmeinsurance_insulin'					=> $request->dmeinsurance_insulin,
		'dmeinsurance_timestest'				=> $request->dmeinsurance_timestest,
		'dmeinsurance_timesinject' 				=> $request->dmeinsurance_timesinject,
		'dmeinsurance_painlocation' 			=> $request->dmeinsurance_painlocation,
		'dmeinsurance_diabeticother' 			=> $request->dmeinsurance_diabeticother,
		'updated_by'	 						=> $request->user_id,
		'updated_at'	 						=> date('Y-m-d h:i:s'),
		]);
		$getorderattachment = DB::table('dmeotherdetails')
		->select('dmeotherdetails_attachment')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->where('status_id','=',1)
		->first();
		// dd($getorderattachment);
		$getattchmentname = "";
		$dmeattachment = array();
		$index=0;
		if ($request->hasFile('dmeotherdetails_attachment')) {
			$ima = $request->dmeotherdetails_attachment;
			foreach ($ima as $imas) {
				if ($imas != "null") {
					if($imas->isValid()){
		            $number = rand(1,999);
			        $numb = $number / 7 ;
					$name = "dmeattachment";
			        $extension = $imas->extension();
		            $dmeattachment[$index] = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		            $dmeattachment[$index] = $imas->move(public_path('dmeattachment/'),$dmeattachment[$index]);
				    $dmeattachment[$index] = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
				    $index++;
				}
	        }
			}
		if ($getorderattachment != null) {
			$getattchmentname = $getorderattachment->dmeotherdetails_attachment;
			$arreyattachmentname = explode(",", $getattchmentname);
			$mergeattachmentarray = array_merge($arreyattachmentname,$dmeattachment);
        	$mergeattachmentname = implode(",", $mergeattachmentarray);
		}else{
			$mergeattachmentname = implode(",", $dmeattachment);
		}
        }else{
           $mergeattachmentname = $getattchmentname; 
        }
        // dd($mergeattachmentname);
		DB::table('dmeotherdetails')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->update([
		'dmeotherdetails_cardiotype' 		=> $request->dmeotherdetails_cardiotype,
		'dmeotherdetails_hearbeat' 			=> $request->dmeotherdetails_hearbeat,
		'dmeotherdetails_levac'				=> $request->dmeotherdetails_levac,
		'dmeotherdetails_stunt' 			=> $request->dmeotherdetails_stunt,
		'dmeotherdetails_heartattack' 		=> $request->dmeotherdetails_heartattack,
		'dmeotherdetails_inlargeheart' 		=> $request->dmeotherdetails_inlargeheart,
		'dmeotherdetails_memer'				=> $request->dmeotherdetails_memer,
		'dmeotherdetails_highcorostol'		=> $request->dmeotherdetails_highcorostol,
		'dmeotherdetails_highlowbp' 		=> $request->dmeotherdetails_highlowbp,
		'dmeotherdetails_dropbp' 			=> $request->dmeotherdetails_dropbp,
		'dmeotherdetails_height' 			=> $request->dmeotherdetails_height,
		'dmeotherdetails_weight' 			=> $request->dmeotherdetails_weight,
		'dmeotherdetails_waist'				=> $request->dmeotherdetails_waist,
		'dmeotherdetails_shoesize' 			=> $request->dmeotherdetails_shoesize,
		'dmeotherdetails_qualifying' 		=> $request->dmeotherdetails_qualifying,
		'dmeotherdetails_uvward' 			=> $request->dmeotherdetails_uvward,
		'dmeotherdetails_allskinissue'		=> $request->dmeotherdetails_allskinissue,
		'dmeotherdetails_skinallergy'		=> $request->dmeotherdetails_skinallergy,
		'dmeotherdetails_dryexzima' 		=> $request->dmeotherdetails_dryexzima,
		'dmeotherdetails_mustskinissue' 	=> $request->dmeotherdetails_mustskinissue,
		'dmeotherdetails_drname'			=> $request->dmeotherdetails_drname,
		'dmeotherdetails_drphone' 			=> $request->dmeotherdetails_drphone,
		'dmeotherdetails_draddress' 		=> $request->dmeotherdetails_draddress,
		'dmeotherdetails_cancerother' 		=> $request->dmeotherdetails_cancerother,
		'dmeotherdetails_hascancer' 		=> $request->dmeotherdetails_hascancer,
		'dmeotherdetails_drnpi' 			=> $request->dmeotherdetails_drnpi,
		'dmeotherdetails_drfaxnumber' 		=> $request->dmeotherdetails_drfaxnumber,
		'dmeotherdetails_merchant' 			=> $request->dmeotherdetails_merchant,
		'dmeotherdetails_comment' 			=> $request->dmeotherdetails_comment,
		'dmeotherdetails_attachment' 		=> $mergeattachmentname,
		'updated_by'	 					=> $request->user_id,
		'updated_at'	 					=> date('Y-m-d h:i:s'),
		]);
		if ($request->role_id == 10) {
			$validate = Validator::make($request->all(), [ 
				'dmeotherdetails_chase'	=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json("Please Select Doctor Chase", 400);
			}
			DB::table('dmeotherdetails')
			->where('dmeclient_id','=',$request->dmeclient_id)
			->update([
			'dmeotherdetails_chase' => $request->dmeotherdetails_chase,
			]);
			if ($request->dmeotherdetails_chase == "yes") {
			$adds[] = array(
			'dmedoctor_name' 		=> $request->dmeotherdetails_drname,
			'dmedoctor_npi' 		=> $request->dmeotherdetails_drnpi,
			'dmedoctor_chase' 		=> "Yes",
			);
			DB::table('dmedoctor')->insert($adds);
			}
			DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		// if ($request->orderstatus_id == 8) {
		// 	$post = [
		// 	'affiliate_agent_name' 				=> $request->user_name,
		// 	'first_name' 						=> $request->dmeclient_name,
		// 	'last_name' 						=> $request->dmeclient_lastname,
		// 	'email_address'						=> $request->dmeclient_email,
		// 	'dob' 								=> $request->dmeclient_dateofbirth,
		// 	'phone_home' 						=> $request->dmeclient_homephone,
		// 	'phone_cell' 						=> $request->dmeclient_cellphone,
		// 	'best_time_to_call'					=> $request->dmeclient_bestcalltime,
		// 	'telemedicine'						=> $request->dmeclient_videochataccess,
		// 	'gender' 							=> $request->dmeclient_gender,
		// 	'address' 							=> $request->dmeclient_address,
		// 	'city' 								=> $request->dmeclient_city,
		// 	'state' 							=> $request->dmeclient_state,
		// 	'zip_code'							=> $request->dmeclient_zip,
		// 	'height_feet' 						=> $request->dmeclient_heightfeet,
		// 	'height_inches' 					=> $request->dmeclient_heightinches,
		// 	'weight' 							=> $request->dmeclient_weight,
		// 	'waist_size'						=> $request->dmeclient_waist,
		// 	'shoe_size'							=> $request->dmeclient_shoesize,
		// 	'primary_insurance_name' 			=> $request->dmeinsurance_insurance,
		// 	'primary_insurance_policy_number' 	=> $request->dmeinsurance_formid,
		// 	'secondary_insurance'				=> $request->dmeinsurance_secondarytype,
		// 	'secondary_insurance_name' 			=> $request->dmeinsurance_secondaryname,
		// 	'secondary_insurance_policy_number' => $request->dmeinsurance_secondarypolicynumber,
		// 	'diabetic' 							=> $request->dmeinsurance_diabetic,
		// 	'diabetic_insulin'					=> $request->dmeinsurance_insulin,
		// 	'diabetic_tests'					=> $request->dmeinsurance_timestest,
		// 	'diabetic_injections' 				=> $request->dmeinsurance_timesinject,
		// 	'pain_location' 					=> $request->dmeinsurance_painlocation,
		// 	];
		// 	$ch = curl_init('https://trueprospects.leadspediatrack.com/post.do?lp_campaign_key=hbyHWdXrpCDqLGZYVfgQ&lp_campaign_id=61265a2bbcc7b');
		// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		// 	$response = curl_exec($ch);
		// 	curl_close($ch);
		// }
		if($request->role_id == 2){
			DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
			]); 
		}if($request->role_id == 3){
			DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_managerlastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		if($save){
			return response()->json(['message' => 'Order Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmedoctorchaseorderlist(Request $request){
		$validate = validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	      'from'			=> 'required',
	      'to'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("fields required", 400);
		}
		$from = $request->from;
		$to = $request->to;
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('dmeclient_chaseby','=', null)
		->whereIn('orderstatus_id',[8,9])
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmedoctorchasepickorderlist(Request $request){
		$validate = validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	      'from'			=> 'required',
	      'to'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("fields required", 400);
		}
		$from = $request->from;
		$to = $request->to;
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('dmeclient_chaseby','=',$request->user_id)
		->where('dmeclient_ispv','=',0)
		->where('dmeclient_isreturnfromchase','=',0)
		->where('orderstatus_id','!=',25)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmedoctorchaseyesorderlist(request $request){
		$validate = validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	      'from'			=> 'required',
	      'to'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("fields required", 400);
		}
		$from = $request->from;
		$to = $request->to;
		$getdmeclientid = db::table('dmeotherdetails')
		->select('dmeclient_id')
		->where('status_id','=',1)
		->where('dmeotherdetails_chase','=',"yes")
		->get();
		$dmeclientids = array();
		foreach ($getdmeclientid as $getdmeclientids) {
			$dmeclientids[] = $getdmeclientids->dmeclient_id;
		}
		$getorderlist = db::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->wherebetween('dmeclient_date', [$from, $to])
		// ->where('dmeclient_chaseby','=',$request->user_id)
		->whereIn('dmeclient_id',$dmeclientids)
		->whereIn('orderstatus_id',[8,9,21,22,23,24,26,27,28,29,30,31,32,33,34])
		->orderby('dmeclient_id','desc')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'order list'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'order not found'],200);
		}
	}
	public function dmedoctorchasenoorderlist(request $request){
		$validate = validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	      'from'			=> 'required',
	      'to'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("fields required", 400);
		}
		$from = $request->from;
		$to = $request->to;
		$getdmeclientid = db::table('dmeotherdetails')
		->select('dmeclient_id')
		->where('status_id','=',1)
		->where('dmeotherdetails_chase','=',"No")
		->get();
		$dmeclientids = array();
		foreach ($getdmeclientid as $getdmeclientids) {
			$dmeclientids[] = $getdmeclientids->dmeclient_id;
		}
		$getorderlist = db::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->wherebetween('dmeclient_date', [$from, $to])
		// ->where('dmeclient_chaseby','=',$request->user_id)
		->whereIn('dmeclient_id',$dmeclientids)
		->whereIn('orderstatus_id',[8,9,21,22,23,24,26,27,28,29,30,31,32,33,34])
		->orderby('dmeclient_id','desc')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'order list'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'order not found'],200);
		}
	}
	public function dmeclientlist(Request $request){
		$getclientlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereIn('orderstatus_id',[8,9,12,19,21,22,23,24,26,27,28,29,30,31,32,33,34])
		->groupBy('dmeinsurance_formid')
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getclientlist){
			return response()->json(['data' => $getclientlist,'message' => 'Client list'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order not found'],200);
		}
	}
	public function dmemyclientlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getclientlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('dmeclient_billingby','=',$request->user_id)
		->whereIn('orderstatus_id',[8,9,12,19,21,22,23,24,26,27,28,29,30,31,32,33,34])
		->groupBy('dmeinsurance_formid')
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getclientlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('dmeclient_chaseby','=',$request->user_id)
		->whereIn('orderstatus_id',[8,9,12,19,21,22,23,24,26,27,28,29,30,31,32,33,34])
		->groupBy('dmeinsurance_formid')
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getclientlist){
			return response()->json(['data' => $getclientlist,'message' => 'Client list'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order not found'],200);
		}
	}
	public function dmenpiclientlist(Request $request){
		$getclientlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('dmeotherdetails_drnpi','=',$request->dmeotherdetails_drnpi)
		->whereIn('orderstatus_id',[8,9,12,19,21,22,23,24,26,27,28,29,30,31,32,33,34])
		->groupBy('dmeinsurance_formid')
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getclientlist){
			return response()->json(['data' => $getclientlist,'message' => 'Filtered Client list'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmeclientorders(Request $request){
		$getclientorders = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('dmeinsurance_formid','=',$request->dmeinsurance_formid)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getclientorders){
			return response()->json(['data' => $getclientorders,'message' => 'Client Orders'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order not found'],200);
		}
	}
	public function dmefilledorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('dmeclient_billingby','=',$request->user_id)
		->where('dmeclient_isfilled','=',1)
		->where('dmeclient_isprocess','=',0)
		->where('dmeclient_isnonprocess','=',0)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmeprocessedorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 3 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->whereIn('orderstatus_id',[8,28,29,30,31,32,33])
		->where('dmeclient_isprocess','=',1)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->whereIn('orderstatus_id',[8,28,29,30,31,32,33])
		->where('dmeclient_billingby','=',$request->user_id)
		->where('dmeclient_isprocess','=',1)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmenonprocessedorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 3 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',8)
		->where('dmeclient_isnonprocess','=',1)
		->where('dmeclient_isprocess','=',0)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',8)
		->where('dmeclient_billingby','=',$request->user_id)
		->where('dmeclient_isnonprocess','=',1)
		->where('dmeclient_isprocess','=',0)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmearchivedorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('dmeclient_billingby','=',$request->user_id)
		->where('dmeclient_isarchived','=',1)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmereturntomanagerorderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('dmeclient_isreturnfromchase','=',1)
		->orwhere('dmeclient_isreturnfrombilling','=',1)
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmedoctornpilist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getlist = DB::table('dmedoctor')
		->select('*')
		->get();
		if($getlist){
			return response()->json(['data' => $getlist,'message' => 'Doctor NPI List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function dmealldeal(Request $request){
		// $getalldata = DB::table('dmeorderdetails')
		// ->select('dmeclient_id','dmeclient_token','dmeclient_name','dmeinsurance_formid','dmeclient_cardtype','dmeclient_date','user_name','orderstatus_id','orderstatus_name')
		// ->where('status_id','=',1)
		// ->where('campaign_id','=',$request->campaign_id)
		// ->orderBy('dmeclient_id','DESC')
		// ->paginate(30);
		$getalldata = DB::table('dmelistdata')
		->select('dmeclient_id','dmeclient_token','dmeclient_name','dmeclient_cardtype','dmeclient_date','orderstatus_id','orderstatus_name')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->orderBy('dmeclient_id','DESC')
		->paginate(30);
		return response()->json(['data' => $getalldata,'message' => 'DME All Deals'],200);
	}
	public function dmedoctorchasecnpvorderlist(Request $request){
		$validate = validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	      'from'			=> 'required',
	      'to'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields required", 400);
		}
		$from = $request->from;
		$to = $request->to;
		$getdmeclientid = DB::table('dmeotherdetails')
		->select('dmeclient_id')
		->where('status_id','=',1)
		->whereNotIn('dmeotherdetails_chase',["yes","no"])
		->get();
		$dmeclientids = array();
		foreach ($getdmeclientid as $getdmeclientids) {
			$dmeclientids[] = $getdmeclientids->dmeclient_id;
		}
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('orderstatus_id','!=',25)
		->where('dmeclient_isreturnfromchase','=',0)
		->where('dmeclient_chaseby','=',$request->user_id)
		->whereIn('dmeclient_id',$dmeclientids)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('dmeclient_ispv','=',1)
		// ->where('dmeclient_iscn','=',0)
		->orderBy('dmeclient_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	// new
	public function updatedmeorderfaxno(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmeclient_id'			=> 'required',
	      'orderstatus_id'			=> 'required',
	      'dmeotherdetails_chase'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		DB::table('dmeclient')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->update([
		'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
		]);
		if($request->dmeotherdetails_chase == "yes"){
			$validate = Validator::make($request->all(), [ 
		      'dmeotherdetails_faxno'	=> 'required',
		      
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fax Number Required", 400);
			}
			$updateorderstatus = DB::table('dmeotherdetails')
			->where('dmeclient_id','=',$request->dmeclient_id)
			->update([
				'dmeotherdetails_chase' => $request->dmeotherdetails_chase,
				'dmeotherdetails_faxno' => $request->dmeotherdetails_faxno,
			]);
			$getdoctordetail = DB::table('dmeotherdetails')
			->select('dmeotherdetails_drname','dmeotherdetails_drnpi')
			->where('dmeclient_id','=',$request->dmeclient_id)
			->where('status_id','=',1)
			->first();
			$doctor[] = array(
			'dmedoctor_name' 		=> $getdoctordetail->dmeotherdetails_drname,
			'dmedoctor_npi' 		=> $getdoctordetail->dmeotherdetails_drnpi,
			'dmedoctor_chase' 		=> "Yes",
			);
			DB::table('dmedoctor')->insert($doctor);
		}else{
		$updateorderstatus = DB::table('dmeotherdetails')
			->where('dmeclient_id','=',$request->dmeclient_id)
			->update([
				'dmeotherdetails_chase' => $request->dmeotherdetails_chase,
			]);
		}
		if($updateorderstatus){
			return response()->json(['message' => 'Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	// new att
	public function uploaddmeattachment(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmeotherdetails_attachment'	=> 'required',
	      'dmeclient_id'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fax Number Required", 400);
		}
		$getorderattachment = DB::table('dmeotherdetails')
		->select('dmeotherdetails_attachment')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->where('status_id','=',1)
		->first();
		$getattchmentname = "";
		$dmeattachment = array();
		$index=0;
		if ($request->hasFile('dmeotherdetails_attachment')) {
			$ima = $request->dmeotherdetails_attachment;
			foreach ($ima as $imas) {
				if ($imas != "null") {
					if($imas->isValid()){
		            $number = rand(1,999);
			        $numb = $number / 7 ;
					$name = "dmeattachment";
			        $extension = $imas->extension();
		            $dmeattachment[$index] = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		            $dmeattachment[$index] = $imas->move(public_path('dmeattachment/'),$dmeattachment[$index]);
				    $dmeattachment[$index] = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
				    $index++;
				}
	        }
			}
		if ($getorderattachment != null) {
			$getattchmentname = $getorderattachment->dmeotherdetails_attachment;
			$arreyattachmentname = explode(",", $getattchmentname);
			$mergeattachmentarray = array_merge($arreyattachmentname,$dmeattachment);
        	$mergeattachmentname = implode(",", $mergeattachmentarray);
		}else{
			$mergeattachmentname = implode(",", $dmeattachment);
		}
        }else{
           $mergeattachmentname = $getattchmentname; 
        }
        $save = DB::table('dmeotherdetails')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->update([
		'dmeotherdetails_attachment' 		=> $mergeattachmentname,
		'updated_by'	 					=> $request->user_id,
		'updated_at'	 					=> date('Y-m-d h:i:s'),
		]);
      	if ($request->role_id == 10) {
			DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
			]); 
		}elseif($request->role_id == 2){
			DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
			]); 
		}elseif($request->role_id == 3){
			DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_managerlastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		if($save){
			return response()->json(['message' => 'Attachment Uploaded Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function activatedmeorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmeclient_id'			=> 'required',
	      'activatetype'			=> 'required',
	      'role_id'					=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if($request->role_id == 2){
			if ($request->activatetype == "Filled") {
				$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
				 'dmeclient_isfilled'				=> 1,
				]); 
			}elseif ($request->activatetype == "Processed") {
				$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
				'dmeclient_isprocess'				=> 1,
				'dmebraces_id' 						=> $request->dmebraces_id,
				'dmeservices_id' 					=> $request->dmeservices_id,
				'dmemerchant_id' 					=> $request->dmemerchant_id,
				]);
			}elseif ($request->activatetype == "NonProcessed") {
				$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
				'dmeclient_isnonprocess'			=> 1,
				]);
			}elseif ($request->activatetype == "Archived") {
				$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
				'dmeclient_isarchived'				=> 1,
				]);
			}elseif ($request->activatetype == "ReturnFromBilling") {
				$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_billinglastupdateddate'	=> date('Y-m-d'),
				'dmeclient_isreturnfrombilling'	=> 1,
				]);
			}elseif ($request->activatetype == "SandSComment") {
				$getid = DB::table('dmeinsurance')
				->select('dmeclient_id')
				->where('status_id','=',1)
				->where('dmeinsurance_formid','=',$request->dmeinsurance_formid)
				->get();
				$sortid = array();
				if (isset($getid)) {
				foreach ($getid as $getids) {
					$sortid[] = $getids->dmeclient_id;
				}
				}
				$updateorderstatus = DB::table('dmeclient')
				->whereIn('dmeclient_id',$sortid)
				->update([
				'dmeclient_sandscomment'	=> $request->dmeclient_sandscomment,
				]);
			}else{
				return response()->json("Type Not Registered", 400);
			}
		}elseif($request->role_id == 10){
			if ($request->activatetype == "PV") {
			$getpharmacy = DB::table('dmeclient')
			->select('dmeclient_pharmacyname')
			->where('dmeclient_id','=',$request->dmeclient_id)
			->first();
			if (isset($getpharmacy->dmeclient_pharmacyname)) {
				$mergepharmacy = $getpharmacy->dmeclient_pharmacyname.','.$request->dmeclient_pharmacyname;	
			}else{
				$mergepharmacy = $request->dmeclient_pharmacyname;
			}
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
				'dmeclient_ispv'					=> 1,
				'dmeclient_pharmacyname'			=> $mergepharmacy,
			]); 
			}elseif ($request->activatetype == "CN") {
			$getpharmacy = DB::table('dmeclient')
			->select('dmeclient_pharmacyname')
			->where('dmeclient_id','=',$request->dmeclient_id)
			->first();
			if (isset($getpharmacy->dmeclient_pharmacyname)) {
				$mergepharmacy = $getpharmacy->dmeclient_pharmacyname.','.$request->dmeclient_pharmacyname;	
			}else{
				$mergepharmacy = $request->dmeclient_pharmacyname;
			}
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
				'dmeclient_iscn'					=> 1,
				'dmeclient_ispvapproval'			=> 1,
				'dmeclient_pharmacyname'			=> $mergepharmacy,
			]); 
			}elseif ($request->activatetype == "CNApproval") {
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
				'dmeclient_iscnapproval'			=> 1,
			]); 
			}elseif ($request->activatetype == "ReturnFromChase") {
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_chaselastupdateddate'	=> date('Y-m-d'),
				'dmeclient_isreturnfromchase'		=> 1,
			]); 
			}else{
				return response()->json("Type Not Registered", 400);
			}
		}else{
				return response()->json("Not Allowed", 400);
		}
		if($updateorderstatus){
			return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmeservices(Request $request){
		$getservices = DB::table('dmeservices')
		->select('*')
		->where('status_id','=',1)
		->get();
		if($getservices){
			return response()->json(['data' => $getservices,'message' => 'Services List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Services Not Found'],200);
		}
	}
	public function dmebraces(Request $request){
		$getbraces = DB::table('dmebraces')
		->select('*')
		->where('status_id','=',1)
		->get();
		if($getbraces){
			return response()->json(['data' => $getbraces,'message' => 'Braces List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Braces Not Found'],200);
		}
	}
	public function updatedmeordercardtype(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmeclient_id'		=> 'required',
	      'dmeclient_cardtype'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus = DB::table('dmeclient')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->update([
		'dmeclient_cardtype'	=> $request->dmeclient_cardtype,
		]); 
		if($updateorderstatus){
			return response()->json(['message' => 'Order Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmeverifyemail(Request $request){
	    $validate = Validator::make($request->all(), [ 
	      'verify_email' 		=> 'required',
	      'verify_password'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Verify Email Password Required", 400);
		}
		$verify = DB::table('user')
		->select('user_id as dmeclient_teamleadid')
		->where('user_email','=',$request->verify_email)
		->where('user_password','=',$request->verify_password)
		->where('status_id','=',1)
		->first();
		if($verify){
			return response()->json(['verify' => $verify,'message' => 'Successfully Verified'],200);
		}else{
			return response()->json("Invalid Email Or Password", 400);
		}
	}
	public function dmecancelorder(Request $request){
	    $validate = Validator::make($request->all(), [ 
	      'dmeclient_id' 		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Client Id Required", 400);
		}
		$updatestatus = DB::table('dmeclient')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->update([
		'orderstatus_id'	=> 12,
		]); 
		if($updatestatus){
			return response()->json(['message' => 'Cancel Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmesavefollowup(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmefollowup_comment'	=> 'required',
	      'dmeclient_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$adds[] = array(
		'dmefollowup_comment' 	=> $request->dmefollowup_comment,
		'dmeclient_id' 			=> $request->dmeclient_id,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('dmefollowup')->insert($adds);
		if($save){
			return response()->json(['message' => 'Followup Saved Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function dmegetfollowup(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmeclient_id'	=> 'required',
	    ]);
	 	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getfollowup = DB::table('getdmefollowup')
		->select('*')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->where('status_id','=',1)
		->get();
		if($getfollowup){
			return response()->json(['data' => $getfollowup,'message' => 'Followup List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmereturntoprocessororderlist(Request $request){
		$from = $request->from;
		$to = $request->to;
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',34)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('orderstatus_id','=',34)
		// ->where('dmeclient_billingby','=',$request->user_id)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
}