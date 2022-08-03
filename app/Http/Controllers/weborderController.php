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

class weborderController extends Controller
{
	public function createweborder(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'client_id'  		=> 'required',
		    'orderstatus_id' 	=> 'required',
		    'weborder_title' 	=> 'required',
		    'weborder_amount' 	=> 'required',
		    'weborder_q1'	 	=> 'required',
		    'weborder_q2'	 	=> 'required',
		    'weborder_q3'	 	=> 'required',
		    'weborder_q4'	 	=> 'required',
		    'weborder_q5'	 	=> 'required',
		    'weborder_q6'	 	=> 'required',
		    'weborder_q7'	 	=> 'required',
		    'weborder_q8'	 	=> 'required',
		    'weborder_q9'	 	=> 'required',
		    'weborder_q10'	 	=> 'required',
		    'weborder_q11'	 	=> 'required',
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
		$adds[] = array(
		'weborder_title'	=> $request->weborder_title,
		'weborder_amount'	=> $request->weborder_amount,
		'weborder_q1' 		=> $request->weborder_q1,
		'weborder_q2'		=> $request->weborder_q2,
		'weborder_q3' 		=> $request->weborder_q3,
		'weborder_q4' 		=> $request->weborder_q4,
		'weborder_q5' 		=> $request->weborder_q5,
		'weborder_q6'		=> $request->weborder_q6,
		'weborder_q7' 		=> $request->weborder_q7,
		'weborder_q8' 		=> $request->weborder_q8,
		'weborder_q9' 		=> $request->weborder_q9,
		'weborder_q10' 		=> $request->weborder_q10,
		'weborder_q11'		=> $request->weborder_q11,
		'weborder_token'	=> $order_token,
		'weborder_date'		=> date('Y-m-d'),
		'client_id' 		=> $request->client_id,
		'campaign_id' 		=> $getcampaignid->campaign_id,
		'orderstatus_id'	=> $request->orderstatus_id,
		'status_id'		 	=> 1,
		'created_by'	 	=> $request->user_id,
		'created_at'	 	=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('weborder')->insert($adds);
		if($save){
			return response()->json(['data' => $adds,'order_token' => $order_token,'message' => 'Order Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function weborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 1 || $request->role_id == 9) {
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('created_by','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		}
		$emptyarray = array();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerforwardedweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',2)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		$emptyarray = array();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Forwarded Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerpickweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',3)
		->where('weborder_managerpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		$emptyarray = array();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Pick Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	 public function unpickweborder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'weborder_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if($request->role_id == 2){
			if ($request->weborder_type == "milestone") {
				$updateorderstatus = DB::table('weborderpayment')
					->where('weborderpayment_id','=',$request->weborderpayment_id )
					->update([
					'orderstatus_id'			=> 8,
					'weborderpayment_billingby'	=> null,
				]); 
			}else{
				$updateorderstatus = DB::table('weborder')
					->where('weborder_id','=',$request->weborder_id)
					->update([
					'orderstatus_id'		=> 8,
					'weborder_billingby'	=> null,
				]); 
			}
		}
		if($request->role_id == 5){
			$updateorderstatus = DB::table('weborder')
				->where('weborder_id','=',$request->weborder_id)
				->update([
				'orderstatus_id'		=> 4,
				'weborder_workpickby'	=> null,
			]); 
		}
		if($request->role_id == 7){
			$updateorderstatus = DB::table('weborder')
				->where('weborder_id','=',$request->weborder_id)
				->update([
				'orderstatus_id'		=> 2,
				'weborder_managerpickby'	=> null,
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Unpick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function pickweborder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'weborder_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if($request->role_id == 2){
			if ($request->weborder_type == "milestone") {
				$updateorderstatus = DB::table('weborderpayment')
					->where('weborderpayment_id','=',$request->weborderpayment_id)
					->update([
					'orderstatus_id'			=> 9,
					'weborderpayment_billingby'	=> $request->user_id,
				]); 
			}else{
				$updateorderstatus = DB::table('weborder')
					->where('weborder_id','=',$request->weborder_id)
					->update([
					'orderstatus_id'		=> 9,
					'weborder_billingby'	=> $request->user_id,
				]); 
			}
		}
		if($request->role_id == 5){
			$updateorderstatus = DB::table('weborder')
				->where('weborder_id','=',$request->weborder_id)
				->update([
				'orderstatus_id'		=> 16,
				'weborder_workpickby'	=> $request->user_id,
			]); 
		}
		if($request->role_id == 7){
			$updateorderstatus = DB::table('weborder')
				->where('weborder_id','=',$request->weborder_id)
				->update([
				'orderstatus_id'			=> 3,
				'weborder_managerpickby'	=> $request->user_id,
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function weborderdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'weborder_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderdetails = DB::table('weborderdetails')
		->select('*')
		->where('weborder_id','=',$request->weborder_id)
		->where('status_id','=',1)
		->first();

		if($getorderdetails){
			return response()->json(['data' => $getorderdetails, 'message' => 'Order Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateweborder(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'orderstatus_id' 	=> 'required',
		    'weborder_id'	 	=> 'required',
		    'weborder_title' 	=> 'required',
		    'weborder_amount' 	=> 'required',
		    'weborder_q1'	 	=> 'required',
		    'weborder_q2'	 	=> 'required',
		    'weborder_q3'	 	=> 'required',
		    'weborder_q4'		=> 'required',
		    'weborder_q5'	 	=> 'required',
		    'weborder_q6'		=> 'required',
		    'weborder_q7'	 	=> 'required',
		    'weborder_q8'		=> 'required',
		    'weborder_q9'		=> 'required',
		    'weborder_q10'	 	=> 'required',
		    'weborder_q11'		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		 $save = DB::table('weborder')
		->where('weborder_id','=',$request->weborder_id)
		->update([
		'weborder_title'	=> $request->weborder_title,
		'weborder_amount'	=> $request->weborder_amount,
		'weborder_q1' 		=> $request->weborder_q1,
		'weborder_q2'		=> $request->weborder_q2,
		'weborder_q3' 		=> $request->weborder_q3,
		'weborder_q4' 		=> $request->weborder_q4,
		'weborder_q5' 		=> $request->weborder_q5,
		'weborder_q6'		=> $request->weborder_q6,
		'weborder_q7'		=> $request->weborder_q7,
		'weborder_q8' 		=> $request->weborder_q8,
		'weborder_q9'		=> $request->weborder_q9,
		'weborder_q10' 		=> $request->weborder_q10,
		'weborder_q11'		=> $request->weborder_q11,
		'orderstatus_id'	=> $request->orderstatus_id,
		'updated_by'	 	=> $request->user_id,
		'updated_at'	 	=> date('Y-m-d h:i:s'),
		]);
		if($save){
			return response()->json(['message' => 'Order Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateweborderstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'weborder_id'		=> 'required',
	      'orderstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus  = DB::table('weborder')
			->where('weborder_id','=',$request->weborder_id)
			->update([
			'orderstatus_id' 		=> $request->orderstatus_id,
		]); 
		if($updateorderstatus){
		return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateweborderpaymentstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'weborderpayment_id'	=> 'required',
	      'orderstatus_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus  = DB::table('weborderpayment')
			->where('weborderpayment_id','=',$request->weborderpayment_id)
			->update([
			'orderstatus_id' 		=> $request->orderstatus_id,
		]); 
		if($updateorderstatus){
		return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function workerforwardedweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',4)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Forwarded Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function workerpickweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',16)
		->where('weborder_workpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Pick Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function workerweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('weborder_workpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerforwardedunpaidweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('weborder_managerpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerunpaidweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->whereNotIn('orderstatus_id', [11,12,18])
		->where('created_by','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerforwardedpaidweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->whereIn('orderstatus_id',[11,18])
		->where('weborder_managerpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerpaidweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->whereIn('orderstatus_id',[11,18])
		->where('created_by','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managerforwardedcancelweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('weborder_managerpickby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function managercancelweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('created_by','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get();
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingforwardedweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',8)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',8)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$finalorderlist = array_merge($getorderlist,$getspecialorderlist);
		if($finalorderlist){
			return response()->json(['data' => $finalorderlist,'message' => 'Forwarded Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingpickweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',9)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',9)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		}else{
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',9)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',9)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		}
		$finalorderlist = array_merge($getorderlist,$getspecialorderlist);
		if($finalorderlist){
			return response()->json(['data' => $finalorderlist,'message' => 'Pick Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingunpaidweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=', 10)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',10)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		}else{
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=', 10)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',10)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		}
		$finalorderlist = array_merge($getorderlist,$getspecialorderlist);
		if($finalorderlist){
			return response()->json(['data' => $finalorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingpaidweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',11)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',11)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		}else{
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',11)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',11)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		}
		$finalorderlist = array_merge($getorderlist,$getspecialorderlist);
		if($finalorderlist){
			return response()->json(['data' => $finalorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingrecoveryweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',18)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',18)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();	
		}else{
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',18)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',18)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		}
		$finalorderlist = array_merge($getorderlist,$getspecialorderlist);
		if($finalorderlist){
			return response()->json(['data' => $finalorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingcancelweborderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('weborder_billingby','=',$request->user_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		}else{
		$getorderlist = DB::table('weborderlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('orderstatus_id','=',12)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborder_id','DESC')
		->get()->toArray();
		}
		$finalorderlist = array_merge($getorderlist,$getspecialorderlist);
		if($finalorderlist){
			return response()->json(['data' => $finalorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function billingsentwebordreinvoice(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'weborder_id'						=> 'required',
	      'orderstatus_id'					=> 'required',
	      'weborder_paypalinvoicenumber'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus  = DB::table('weborder')
			->where('weborder_id','=',$request->weborder_id)
			->update([
			'orderstatus_id' 				=> $request->orderstatus_id,
			'weborder_paypalinvoicenumber' => $request->weborder_paypalinvoicenumber,
		]); 
		if($updateorderstatus){
		return response()->json(['message' => 'Invoice Sent Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function billingsentwebordrepaymentinvoice(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'weborderpayment_id'					=> 'required',
	      'orderstatus_id'						=> 'required',
	      'weborderpayment_paypalinvoicenumber'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus  = DB::table('weborderpayment')
			->where('weborderpayment_id','=',$request->weborderpayment_id)
			->update([
			'orderstatus_id' 						=> $request->orderstatus_id,
			'weborderpayment_paypalinvoicenumber' 	=> $request->weborderpayment_paypalinvoicenumber,
		]); 
		if($updateorderstatus){
		return response()->json(['message' => 'Invoice Sent Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatewebpaypalinvoicenumber(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'weborder_paypalinvoicenumber'	=> 'required',
	      'weborder_id'						=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields are Required", 400);
		}
		$validate = Validator::make($request->all(), [ 
	    ]);
     	$updatepaypal = DB::table('weborder')
			->where('weborder_id','=',$request->weborder_id)
			->update([
			'weborder_paypalinvoicenumber' 	=> $request->weborder_paypalinvoicenumber,
			'updated_by'	 				=> $request->user_id,
			'updated_at'	 				=> date('Y-m-d h:i:s'),
		]);
		if($updatepaypal){
			return response()->json(['message' => 'Paypal Invoice Number Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function updatewebpaymentpaypalinvoicenumber(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'weborderpayment_paypalinvoicenumber'	=> 'required',
	      'weborderpayment_id'					=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields are Required", 400);
		}
		$validate = Validator::make($request->all(), [ 
	    ]);
     	$updatepaypal = DB::table('weborderpayment')
			->where('weborderpayment_id','=',$request->weborderpayment_id)
			->update([
			'weborderpayment_paypalinvoicenumber' 	=> $request->weborderpayment_paypalinvoicenumber,
		]);
		if($updatepaypal){
			return response()->json(['message' => 'Paypal Invoice Number Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function requestweborderpayment(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'weborderpayment_amount' 	=> 'required',
		    'orderstatus_id'			=> 'required',
		    'weborder_id'				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		 $adds[] = array(
		'weborderpayment_amount' 	=> $request->weborderpayment_amount,
		'weborder_id' 				=> $request->weborder_id,
		'orderstatus_id' 			=> $request->orderstatus_id,
		'weborderpayment_date'		=> date('Y-m-d'),
		'status_id'		 			=> 1,
		'created_by'	 			=> $request->user_id,
		'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('weborderpayment')->insert($adds);
		$getsumwebpaymentamount = DB::table('weborderpayment')
		->select('weborderpayment_amount')
		->where('weborder_id','=',$request->weborder_id)
		->where('status_id','=',1)
		->sum('weborderpayment_amount');
		$getweborderamount = DB::table('weborder')
		->select('weborder_amount')
		->where('weborder_id','=',$request->weborder_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->first();
		$getremainingweborderamount = $getweborderamount->weborder_amount-$getsumwebpaymentamount;
		$updateremainingamount = DB::table('weborder')
			->where('weborder_id','=',$request->weborder_id)
			->update([
			'weborder_remainingamount' 	=> $getremainingweborderamount,
		]);
		if($save){
			return response()->json(['message' => 'Request Forwarded Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function webpaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'weborder_id'		=> 'required',
	      'campaign_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getspecialorderlist = DB::table('weborderpaymentlist')
		->select('*')
		->where('weborder_id','=',$request->weborder_id)
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('weborderpayment_id','DESC')
		->get()->toArray();
		if($getspecialorderlist){
			return response()->json(['data' => $getspecialorderlist,'message' => 'Web Payment List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Web Payment Not Found'],200);
		}
	}
}