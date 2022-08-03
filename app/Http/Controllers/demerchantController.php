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

class demerchantController extends Controller
{
	public function adddemerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'demerchant_name'  				=> 'required',
		    'demerchant_location' 			=> 'required',
		    'demerchant_number' 			=> 'required',
	    	'demerchant_rate'  				=> 'required',
		    'demerchant_commissionpercent'	=> 'required',
		    'demerchant_service' 			=> 'required',
		    'demerchant_comment'			=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$adds[] = array(
		'demerchant_name'				=> $request->demerchant_name,
		'demerchant_location'			=> $request->demerchant_location,
		'demerchant_number'				=> $request->demerchant_number,
		'demerchant_rate'				=> $request->demerchant_rate,
		'demerchant_commissionpercent'	=> $request->demerchant_commissionpercent,
		'demerchant_service' 			=> $request->demerchant_service,
		'demerchant_comment' 			=> $request->demerchant_comment,
		'status_id'		 				=> 1,
		'created_by'	 				=> $request->user_id,
		'created_at'	 				=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('demerchant')->insert($adds);
		if($save){
			return response()->json(['message' => 'Merchant Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function demerchantlist(Request $request){
		if($request->role_id == 1){
			$getmerchant = DB::table('demerchant')
			->select('*')
			->get();	
		}else{
			$getmerchant = DB::table('demerchant')
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
	public function updatedemerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'demerchant_id'  				=> 'required',
	    	'demerchant_name'  				=> 'required',
	    	'demerchant_location'  			=> 'required',
	    	'demerchant_number'  			=> 'required',
	    	'demerchant_rate'  				=> 'required',
		    'demerchant_commissionpercent' 	=> 'required',
		    'demerchant_service' 			=> 'required',
		    'demerchant_comment'		 	=> 'required',
		    
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorder  = DB::table('demerchant')
		->where('demerchant_id','=',$request->demerchant_id)
		->update([
		'demerchant_name'				=> $request->demerchant_name,
		'demerchant_location'			=> $request->demerchant_location,
		'demerchant_number'				=> $request->demerchant_number,
		'demerchant_rate'				=> $request->demerchant_rate,
		'demerchant_commissionpercent' 	=> $request->demerchant_commissionpercent,
		'demerchant_service' 			=> $request->demerchant_service,
		'demerchant_comment' 			=> $request->demerchant_comment,
		'updated_by'	 				=> $request->user_id,
		'updated_at'	 				=> date('Y-m-d h:i:s'),
		]);
		if($updateorder){
			return response()->json(['message' => 'Merchant Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deupdatemerchantactivestatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'demerchant_id'  			=> 'required',
	        'status_id'	 				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorder  = DB::table('demerchant')
		->where('demerchant_id','=',$request->demerchant_id)
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