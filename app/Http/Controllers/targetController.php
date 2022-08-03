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

class targetController extends Controller
{
	public function addtarget(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'usertarget_target'  	=> 'required',
	    	'usertarget_month'  	=> 'required',
	    	'usertarget_userid'  	=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$addtarget[] = array(
		'usertarget_target'		=> $request->usertarget_target,
		'usertarget_month'		=> $request->usertarget_month,
		'user_id' 				=> $request->usertarget_userid,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		DB::table('usertarget')->insert($addtarget);
		if($addtarget){
			return response()->json(['message' => 'Target Added Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function updatetarget(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'usertarget_id'  		=> 'required',
	    	'usertarget_target'  	=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updatetarget = DB::table('usertarget')
			->where('usertarget_id','=',$request->usertarget_id)
			->update([
			'usertarget_target'			=> $request->usertarget_target,
		]); 
		if($updatetarget){
			return response()->json(['message' => 'Target Updated Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function targetlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'usertarget_month'  => 'required',
	    	'campaign_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$gettargetlist = DB::table('targetlist')
		->select('*')
		->where('usertarget_month','=',$request->usertarget_month)
		->where('status_id','=',1)
		->get();	
		$targetedemployee = array();
		foreach ($gettargetlist as $gettargetlists) {
			$targetedemployee[] = $gettargetlists->user_id;
		}
		$getnontargetlist = DB::table('user')
		->select('*')
		->whereNotIn('user_id', $targetedemployee)
		->where('role_id','>',2)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();	
		if($gettargetlist){
			return response()->json(['targetemployeedata' => $gettargetlist, 'nontargetemployeedata' => $getnontargetlist, 'message' => 'Target List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function usertargetlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'target_userid'		=> 'required',
	    	'campaign_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getusertargetlist = DB::table('targetlist')
		->select('*')
		->where('user_id','=',$request->target_userid)
		->where('status_id','=',1)
		->get();	
		if($getusertargetlist){
			return response()->json(['usertargetata' => $getusertargetlist, 'message' => 'User Target List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function addcommission(Request $request){
		// dd($request->commission);
		$validate = Validator::make($request->all(), [ 
	    	'commission'  	=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$multiple = $request->commission;
		foreach ($multiple as $multiples) {
		$addcommission[] = array(
		'commission_from'	=> $multiples['commission_from'],
		'commission_to'		=> $multiples['commission_to'],
		'commission_rate' 	=> $multiples['commission_rate'],
		'user_id' 			=> $request->id,
		'role_id' 			=> $request->role_id,
		'status_id'		 	=> 1,
		'created_by'	 	=> $request->user_id,
		'created_at'	 	=> date('Y-m-d h:i:s'),
		);
		}
		DB::table('commission')->insert($addcommission);
		if($addcommission){
			return response()->json(['message' => 'Commission Added Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function commissionlist(Request $request){
		$getcommissionlist = DB::table('commissionlist')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->get();	
		if($getcommissionlist){
			return response()->json(['commissiondata' => $getcommissionlist, 'message' => 'Commission List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
}