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

class merchantController extends Controller
{
	public function addmerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'dmemerchant_name'  		=> 'required',
	    	'dmemerchant_type'  		=> 'required',
	    	'dmemerchant_rate'  		=> 'required',
		    'dmemerchant_agerangefrom' 	=> 'required',
		    'dmemerchant_agerangeto' 	=> 'required',
		    'dmemerchant_form'		 	=> 'required',
		    'dmemerchant_trackingform' 	=> 'required',
		    'dmemerchant_duplicateform'	=> 'required',
		    'dmemerchant_transfernumber'=> 'required',
		    'dmemerchant_ltnumber'	 	=> 'required',
		    'dmemerchant_centercode' 	=> 'required',
		    'dmeservices_id' 			=> 'required',
		    'state_id'	 				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$numbers = range($request->dmemerchant_agerangefrom, $request->dmemerchant_agerangeto);
		$getrange = array();
		foreach ($numbers as $number) {
		    $getrange[] = $number;
		}
		$setrange = implode(',', $getrange);
		$adds[] = array(
		'dmemerchant_name'			=> $request->dmemerchant_name,
		'dmemerchant_type'			=> $request->dmemerchant_type,
		'dmemerchant_rate'			=> $request->dmemerchant_rate,
		'dmemerchant_agerange'		=> $setrange,
		'dmemerchant_agerangefrom'	=> $request->dmemerchant_agerangefrom,
		'dmemerchant_agerangeto' 	=> $request->dmemerchant_agerangeto,
		'dmemerchant_form' 			=> $request->dmemerchant_form,
		'dmemerchant_trackingform' 	=> $request->dmemerchant_trackingform,
		'dmemerchant_duplicateform' => $request->dmemerchant_duplicateform,
		'dmemerchant_transfernumber'=> $request->dmemerchant_transfernumber,
		'dmemerchant_ltnumber' 		=> $request->dmemerchant_ltnumber,
		'dmemerchant_centercode' 	=> $request->dmemerchant_centercode,
		'dmeservices_id'			=> $request->dmeservices_id,
		'dmebraces_id'				=> $request->dmebraces_id,
		'state_id' 					=> $request->state_id,
		'status_id'		 			=> 1,
		'created_by'	 			=> $request->user_id,
		'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('dmemerchant')->insert($adds);
		if($save){
			return response()->json(['message' => 'Merchant Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmemerchantlist(Request $request){
		if($request->role_id == 1){
			$getmerchant = DB::table('dmemerchant')
			->select('*')
			->get();	
		}else{
		$getmerchant = DB::table('dmemerchant')
		->select('*')
		->where('status_id','=',1)
		->get();
		}
		if($getmerchant){
			return response()->json(['data' => $getmerchant,'message' => 'Merchant List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Merchant Not Found'],200);
		}
	}
	public function updatemerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'dmemerchant_id'  			=> 'required',
	    	'dmemerchant_name'  		=> 'required',
	    	'dmemerchant_type'  		=> 'required',
	    	'dmemerchant_rate'  		=> 'required',
		    'dmemerchant_agerangefrom' 	=> 'required',
		    'dmemerchant_agerangeto' 	=> 'required',
		    'dmemerchant_form'		 	=> 'required',
		    'dmemerchant_trackingform' 	=> 'required',
		    'dmemerchant_duplicateform'	=> 'required',
		    'dmemerchant_transfernumber'=> 'required',
		    'dmemerchant_ltnumber'	 	=> 'required',
		    'dmemerchant_centercode' 	=> 'required',
		    'dmeservices_id' 			=> 'required',
		    'state_id'	 				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$numbers = range($request->dmemerchant_agerangefrom, $request->dmemerchant_agerangeto);
		$getrange = array();
		foreach ($numbers as $number) {
		    $getrange[] = $number;
		}
		$setrange = implode(',', $getrange);
		$updateorder  = DB::table('dmemerchant')
		->where('dmemerchant_id','=',$request->dmemerchant_id)
		->update([
		'dmemerchant_name'			=> $request->dmemerchant_name,
		'dmemerchant_type'			=> $request->dmemerchant_type,
		'dmemerchant_rate'			=> $request->dmemerchant_rate,
		'dmemerchant_agerange'		=> $setrange,
		'dmemerchant_agerangefrom'	=> $request->dmemerchant_agerangefrom,
		'dmemerchant_agerangeto' 	=> $request->dmemerchant_agerangeto,
		'dmemerchant_form' 			=> $request->dmemerchant_form,
		'dmemerchant_trackingform' 	=> $request->dmemerchant_trackingform,
		'dmemerchant_duplicateform' => $request->dmemerchant_duplicateform,
		'dmemerchant_transfernumber'=> $request->dmemerchant_transfernumber,
		'dmemerchant_ltnumber' 		=> $request->dmemerchant_ltnumber,
		'dmemerchant_centercode' 	=> $request->dmemerchant_centercode,
		'dmeservices_id'			=> $request->dmeservices_id,
		'dmebraces_id'				=> $request->dmebraces_id,
		'state_id' 					=> $request->state_id,
		'created_by'	 			=> $request->user_id,
		'created_at'	 			=> date('Y-m-d h:i:s'),
		]);
		if($updateorder){
			return response()->json(['message' => 'Merchant Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmemerchantchecker(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'dmeclient_dateofbirth' 	=> 'required',
		    'dmeservices_id' 			=> 'required',
		    'dmeclient_state'	 		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getmerchant = DB::table('dmemerchant')
		->select('*')
		->where('status_id','=',1)
		->get();
		$sortmerchant = array();
		$index = 0;
		foreach ($getmerchant as $getmerchants) {
			$getstate = DB::table('dmemerchant')
			->select('*')
			->where('state_id','like','%'.$request->dmeclient_state.'%')
			->where('dmemerchant_id','=',$getmerchants->dmemerchant_id)
			->where('status_id','=',1)
			->first();
			if (isset($getstate)) {
				$getmerchants->state = "True";
			}else{
				$getmerchants->state = "False";
			}
			$getage = DB::table('dmemerchant')
			->select('*')
			->where('dmemerchant_agerange','like','%'.$request->dmeclient_dateofbirth.'%')
			->where('dmemerchant_id','=',$getmerchants->dmemerchant_id)
			->where('status_id','=',1)
			->first();
			if (isset($getage)) {
				$getmerchants->age = "True";
			}else{
				$getmerchants->age = "False";
			}
			$getservices = DB::table('dmemerchant')
			->select('*')
			->where('dmeservices_id','like','%'.$request->dmeservices_id.'%')
			->where('dmemerchant_id','=',$getmerchants->dmemerchant_id)
			->where('status_id','=',1)
			->first();
			if (isset($getservices)) {
				$getmerchants->service = "True";
			}else{
				$getmerchants->service = "False";
			}
		$sortmerchant[$index] = $getmerchants;
		$index++;
		}
		if($sortmerchant){
			return response()->json(['data' => $sortmerchant,'message' => 'Merchant Checker List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Merchant Not Found'],200);
		}
	}
	public function dmemerchantservices(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'dmemerchant_id' 	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getmerchant = DB::table('dmemerchant')
		->select('*')
		->where('dmemerchant_id','=',$request->dmemerchant_id)
		->where('status_id','=',1)
		->first();
		$sortmerchantservice = explode(',', $getmerchant->dmeservices_id);
		$services = array();
		foreach ($sortmerchantservice as $sortmerchantservices) {
			$services[] = DB::table('dmeservices')
			->select('*')
			->where('dmeservices_id','=',$sortmerchantservices)
			->where('status_id','=',1)
			->first();
		}
		if($services){
			return response()->json(['data' => $services,'message' => 'Merchant List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Merchant Not Found'],200);
		}
	}
	public function dmemerchantdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'dmemerchant_id' 	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getmerchant = DB::table('dmemerchant')
		->select('*')
		->where('dmemerchant_id','=',$request->dmemerchant_id)
		->where('status_id','=',1)
		->first();
		$sortmerchantservice = explode(',', $getmerchant->dmeservices_id);
		$services = array();
		$sortservices = array();
		foreach ($sortmerchantservice as $sortmerchantservices) {
			$services[] = DB::table('dmeservices')
			->select('dmeservices_name')
			->where('dmeservices_id','=',$sortmerchantservices)
			->where('status_id','=',1)
			->first();
		}
		foreach ($services as $servicess) {
			$sortservices[] = $servicess->dmeservices_name;
 		}
 		if (isset($getmerchant->dmebraces_id)) {
 		$sortmerchantbrace = explode(',', $getmerchant->dmebraces_id);
 		$braces = array();
		$sortbraces = array();
		foreach ($sortmerchantbrace as $sortmerchantbraces) {
			$braces[] = DB::table('dmebraces')
			->select('dmebraces_name')
			->where('dmebraces_id','=',$sortmerchantbraces)
			->where('status_id','=',1)
			->first();
		}
		foreach ($braces as $bracess) {
			$sortbraces[] = $bracess->dmebraces_name;
 		}
 		}else{
 			$sortbraces = array();
 		}
		$sortmerchantstate = explode(',', $getmerchant->state_id);
		$states = array();
		$sortstates = array();
		foreach ($sortmerchantstate as $sortmerchantstates) {
			$states[] = DB::table('state')
			->select('state_code as state_name')
			->where('state_id','=',$sortmerchantstates)
			->where('status_id','=',1)
			->first();
		}
		foreach ($states as $statess) {
			$sortstates[] = $statess->state_name;
		}
		if($services){
			return response()->json(['data' => $getmerchant, 'services' => $sortservices, 'braces' => $sortbraces, 'states' => $sortstates, 'message' => 'Merchant Details'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Merchant Detail Not Found'],200);
		}
	}
	public function dmeupdatemerchantactivestatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'dmemerchant_id'  			=> 'required',
	        'status_id'	 				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorder  = DB::table('dmemerchant')
		->where('dmemerchant_id','=',$request->dmemerchant_id)
		->update([
		'status_id'		 			=> $request->status_id,
		'updated_by'	 			=> $request->user_id,
		'updated_at'	 			=> date('Y-m-d h:i:s'),
		]);
		if($updateorder){
			return response()->json(['message' => 'Merchant Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}