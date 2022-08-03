<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Image;
use DB;
use Input;
use App\Item;
use Session;
use Response;
use Validator;
use ZipArchive;

class decrmController extends Controller
{
	public function decheckclient(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'declient_homephone'  	=> 'required',
	    	'campaign_id'  			=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getclientinfo = DB::table('declient')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('declient_homephone','=',$request->declient_homephone)
		->where('status_id','=',1)
		->first();
		if($getclientinfo){
			return response()->json(['data' => $getclientinfo,'message' => 'Client Details'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Client Details'],200);
		}
	}
	public function decreatedeal(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'deitem_dealid'  	=> 'required',
	    	'campaign_id'  		=> 'required', 
	    	'deorderstatus_id'  => 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$depayment_token = mt_rand(1000000, 9999999);
		$addclient[] = array(
				'declient_name' 		=> $request->declient_name,
				'declient_homephone'	=> $request->declient_homephone,
				'declient_address' 		=> $request->declient_address,
				'declient_workphone'	=> $request->declient_workphone,
				'declient_city' 		=> $request->declient_city,
				'declient_country'		=> $request->declient_country,
				'declient_zip' 			=> $request->declient_zip,
				'declient_state' 		=> $request->declient_state,
				'declient_email' 		=> $request->declient_email,
				'declient_sin' 			=> $request->declient_sin,
				'declient_dob'			=> $request->declient_dob,
				'declient_mmin' 		=> $request->declient_mmin,
				'declient_servicename' 	=> $request->declient_servicename,
				'declient_servicefee' 	=> $request->declient_servicefee,
				'demerchant_id' 		=> $request->demerchant_id,
				'declient_date'			=> date('Y-m-d'),
				'campaign_id' 			=> $request->campaign_id,
				'status_id'		 		=> 1,
				'created_by'	 		=> $request->user_id,
				'created_at'	 		=> date('Y-m-d h:i:s'),
				);
		$saveclient = DB::table('declient')->insert($addclient);
		$declient_id = DB::getPdo()->lastInsertId();
		$multiple = $request->payment;
		// dd($request);
		foreach($multiple as $multiples){
		$addpayment[] = array(
				'depayment_title' 			=> $multiples['depayment_title'],
				'depayment_ccnumber' 		=> $multiples['depayment_ccnumber'],
				'depayment_cvc'				=> $multiples['depayment_cvc'],
				'depayment_exp' 			=> $multiples['depayment_exp'],
				'depayment_apr'				=> $multiples['depayment_apr'],
				'depayment_owe' 			=> $multiples['depayment_owe'],
				'depayment_awail'			=> $multiples['depayment_awail'],
				'depayment_bank' 			=> $multiples['depayment_bank'],
				'depayment_banktollfree' 	=> $multiples['depayment_banktollfree'],
				'depayment_minpay' 			=> $multiples['depayment_minpay'],
				'depayment_lastpay' 		=> $multiples['depayment_lastpay'],
				'depayment_currentpay'		=> $multiples['depayment_currentpay'],
				'depayment_nextpay' 		=> $multiples['depayment_nextpay'],
				'depayment_lastpaymentdate' => $multiples['depayment_lastpaymentdate'],
				'depayment_nextpaymentdate'	=> $multiples['depayment_nextpaymentdate'],
				'depayment_token'			=> $depayment_token,
				'declient_id' 				=> $declient_id,
				'deorderstatus_id' 			=> $request->deorderstatus_id,
				'status_id'		 			=> 1,
				'created_by'	 			=> $request->user_id,
				'created_at'	 			=> date('Y-m-d h:i:s'),
				);
		}
		DB::table('depayment')->insert($addpayment);
		$additem[] = array(
				'deitem_dealid' 		=> $request->deitem_dealid,
				'deitem_product'		=> $request->deitem_product,
				'deitem_quantity' 		=> $request->deitem_quantity,
				'deitem_price'			=> $request->deitem_price,
				'deitem_paymentoption' 	=> $request->deitem_paymentoption,
				'declient_id' 			=> $declient_id,
				'status_id'		 		=> 1,
				'created_by'	 		=> $request->user_id,
				'created_at'	 		=> date('Y-m-d h:i:s'),
				);
		DB::table('deitem')->insert($additem);
		$addnote[] = array(
				'denotes_agentnote'		=> $request->denotes_agentnote,
				'denotes_managernote'	=> $request->denotes_managernote,
				'denotes_customernote' 	=> $request->denotes_customernote,
				'declient_id' 			=> $declient_id,
				'status_id'		 		=> 1,
				'created_by'	 		=> $request->user_id,
				'created_at'	 		=> date('Y-m-d h:i:s'),
				);
		DB::table('denotes')->insert($addnote);
		if($saveclient){
			return response()->json(['message' => 'Deal Created Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function deupdatedeal(Request $request){
		// dd($request);
		$validate = Validator::make($request->all(), [ 
	    	'deitem_dealid'  	=> 'required',
	    	'deorderstatus_id'  => 'required',
	    	'declient_id'  		=> 'required',
	    	'deitem_id'  		=> 'required',
	    	'denotes_id'  		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
			$updatedeal  = DB::table('declient')
				->where('declient_id','=',$request->declient_id)
				->update([
				'declient_name' 		=> $request->declient_name,
				'declient_homephone'	=> $request->declient_homephone,
				'declient_address' 		=> $request->declient_address,
				'declient_workphone'	=> $request->declient_workphone,
				'declient_city' 		=> $request->declient_city,
				'declient_country'		=> $request->declient_country,
				'declient_zip' 			=> $request->declient_zip,
				'declient_state' 		=> $request->declient_state,
				'declient_email' 		=> $request->declient_email,
				'declient_sin' 			=> $request->declient_sin,
				'declient_dob'			=> $request->declient_dob,
				'declient_mmin' 		=> $request->declient_mmin,
				'declient_servicename' 	=> $request->declient_servicename,
				'declient_servicefee' 	=> $request->declient_servicefee,
				'demerchant_id' 		=> $request->demerchant_id,
				'status_id'		 		=> 1,
				'updated_by'	 		=> $request->user_id,
				'updated_at'	 		=> date('Y-m-d h:i:s'),
				]);
		// dd($request);
		$multiple = $request->payment;
		foreach($multiple as $multiples){
		if ($multiples['depayment_id'] != "-") {
		$updatedeal  = DB::table('depayment')
				->where('depayment_id','=',$multiples['depayment_id'])
				->update([
				'depayment_title' 			=> $multiples['depayment_title'],
				'depayment_ccnumber' 		=> $multiples['depayment_ccnumber'],
				'depayment_cvc'				=> $multiples['depayment_cvc'],
				'depayment_exp' 			=> $multiples['depayment_exp'],
				'depayment_apr'				=> $multiples['depayment_apr'],
				'depayment_owe' 			=> $multiples['depayment_owe'],
				'depayment_awail'			=> $multiples['depayment_awail'],
				'depayment_bank' 			=> $multiples['depayment_bank'],
				'depayment_banktollfree' 	=> $multiples['depayment_banktollfree'],
				'depayment_minpay' 			=> $multiples['depayment_minpay'],
				'depayment_lastpay' 		=> $multiples['depayment_lastpay'],
				'depayment_currentpay'		=> $multiples['depayment_currentpay'],
				'depayment_nextpay' 		=> $multiples['depayment_nextpay'],
				'depayment_lastpaymentdate' => $multiples['depayment_lastpaymentdate'],
				'depayment_nextpaymentdate'	=> $multiples['depayment_nextpaymentdate'],
				'declient_id' 				=> $request->declient_id,
				'deorderstatus_id' 			=> $request->deorderstatus_id,
				'status_id'		 			=> 1,
				'updated_by'	 			=> $request->user_id,
				'updated_at'	 			=> date('Y-m-d h:i:s'),
				]);
		}else{
				$addpayment = array(
				'depayment_title' 			=> $multiples['depayment_title'],
				'depayment_ccnumber' 		=> $multiples['depayment_ccnumber'],
				'depayment_cvc'				=> $multiples['depayment_cvc'],
				'depayment_exp' 			=> $multiples['depayment_exp'],
				'depayment_apr'				=> $multiples['depayment_apr'],
				'depayment_owe' 			=> $multiples['depayment_owe'],
				'depayment_awail'			=> $multiples['depayment_awail'],
				'depayment_bank' 			=> $multiples['depayment_bank'],
				'depayment_banktollfree' 	=> $multiples['depayment_banktollfree'],
				'depayment_minpay' 			=> $multiples['depayment_minpay'],
				'depayment_lastpay' 		=> $multiples['depayment_lastpay'],
				'depayment_currentpay'		=> $multiples['depayment_currentpay'],
				'depayment_nextpay' 		=> $multiples['depayment_nextpay'],
				'depayment_lastpaymentdate' => $multiples['depayment_lastpaymentdate'],
				'depayment_nextpaymentdate'	=> $multiples['depayment_nextpaymentdate'],
				'depayment_token'			=> $request->depayment_token,
				'declient_id' 				=> $request->declient_id,
				'deorderstatus_id' 			=> $request->deorderstatus_id,
				'status_id'		 			=> 1,
				'created_by'	 			=> $request->user_id,
				'created_at'	 			=> date('Y-m-d h:i:s'),
				);
		DB::table('depayment')->insert($addpayment);
		}
		}
		$updatedeal  = DB::table('deitem')
				->where('deitem_id','=',$request->deitem_id)
				->update([
				'deitem_dealid' 		=> $request->deitem_dealid,
				'deitem_product'		=> $request->deitem_product,
				'deitem_quantity' 		=> $request->deitem_quantity,
				'deitem_price'			=> $request->deitem_price,
				'deitem_paymentoption' 	=> $request->deitem_paymentoption,
				'declient_id' 			=> $request->declient_id,
				'status_id'		 		=> 1,
				'updated_by'	 		=> $request->user_id,
				'updated_at'	 		=> date('Y-m-d h:i:s'),
				]);
		$updatedeal  = DB::table('denotes')
				->where('denotes_id','=',$request->denotes_id)
				->update([
				'denotes_agentnote'		=> $request->denotes_agentnote,
				'denotes_managernote'	=> $request->denotes_managernote,
				'denotes_customernote' 	=> $request->denotes_customernote,
				'declient_id' 			=> $request->declient_id,
				'status_id'		 		=> 1,
				'updated_by'	 		=> $request->user_id,
				'updated_at'	 		=> date('Y-m-d h:i:s'),
				]);
		if($updatedeal){
			return response()->json(['message' => 'Deal Updated Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function dedeallist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'role_id'  		=> 'required',
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'campaign_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$getorderlist = DB::table('dedeallist')
		->select('*')
		->where('declient_id','>',24061)
		->whereBetween('declient_date', [$request->from, $request->to])
		->where('campaign_id','=',$request->campaign_id)
		->where('depayment_billingpickby','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('declient_id')
		->orderBy('declient_id','DESC')
		->get();	
		}elseif($request->role_id == 3){
		$validate = Validator::make($request->all(), [ 
	    	'deorderstatus_id'  => 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dedeallist')
		->select('*')
		->where('declient_id','>',24061)
		->whereBetween('declient_date', [$request->from, $request->to])
		->where('campaign_id','=',$request->campaign_id)
		->where('created_by','=',$request->user_id)
		->where('deorderstatus_id','=',$request->deorderstatus_id)
		->where('status_id','=',1)
		->groupBy('declient_id')
		->orderBy('declient_id','DESC')
		->get();	
		}elseif ($request->campaign_id == 5) {
		$getorderlist = DB::table('dedeallist')
		->select('*')
		->where('declient_id','>',24061)
		->whereBetween('declient_date', [$request->from, $request->to])
		->where('campaign_id','=',$request->campaign_id)
		->where('created_by','=',$request->user_id)
		->where('status_id','=',1)
		->groupBy('declient_id')
		->orderBy('declient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dedeallist')
		->select('*')
		->where('declient_id','>',24061)
		->whereBetween('declient_date', [$request->from, $request->to])
		->where('campaign_id','=',$request->campaign_id)
		->where('created_by','=',$request->user_id)
		->whereNotIn('deorderstatus_id',[9,12])
		->where('status_id','=',1)
		->groupBy('declient_id')
		->orderBy('declient_id','DESC')
		->get();	
		}
		$getorderlist = $this->paginate($getorderlist);
		if($getorderlist){
			return response()->json(['data' => $getorderlist, 'message' => 'De Deal List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function deforwardeddeallist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'campaign_id'  		=> 'required',
	    	'deorderstatus_id'  => 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if($request->role_id == 2){
			if ($request->deorderstatus_id == 4) {
				$deforwardeddeallist = DB::table('deforwardeddeallist')
				->select('*')
				->where('declient_id','>',24061)
				->whereBetween('declient_date', [$request->from, $request->to])
				->where('campaign_id','=',$request->campaign_id)
				->where('deorderstatus_id','=',$request->deorderstatus_id)
				->where('status_id','=',1)
				->groupBy('declient_id')
				->orderBy('declient_id','DESC')
				->get();
			}else{
				$deforwardeddeallist = DB::table('deforwardeddeallist')
				->select('*')
				->where('declient_id','>',24061)
				->whereBetween('declient_date', [$request->from, $request->to])
				->where('depayment_billingpickby','=',$request->user_id)
				->where('campaign_id','=',$request->campaign_id)
				->where('deorderstatus_id','=',$request->deorderstatus_id)
				->where('status_id','=',1)
				->groupBy('declient_id')
				->orderBy('declient_id','DESC')
				->get();
			}
		}elseif($request->role_id == 4){
			$deforwardeddeallist = DB::table('deforwardeddeallist')
			->select('*')
			->where('declient_id','>',24061)
			->whereBetween('declient_date', [$request->from, $request->to])
			->where('created_by','=',$request->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->where('deorderstatus_id','=',$request->deorderstatus_id)
			->where('status_id','=',1)
			->groupBy('declient_id')
			->orderBy('declient_id','DESC')
			->get();
		}else{
			$deforwardeddeallist = DB::table('deforwardeddeallist')
			->select('*')
			->where('declient_id','>',24061)
			->whereBetween('declient_date', [$request->from, $request->to])
			->where('campaign_id','=',$request->campaign_id)
			->where('deorderstatus_id','=',$request->deorderstatus_id)
			->where('status_id','=',1)
			->groupBy('declient_id')
			->orderBy('declient_id','DESC')
			->get();	
		}
		$deforwardeddeallist = $this->paginate($deforwardeddeallist);
		if($deforwardeddeallist){
			return response()->json(['data' => $deforwardeddeallist, 'message' => 'De Forwarded Deal List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function depickdeallist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'role_id'		 	=> 'required',
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'campaign_id'  		=> 'required',
	    	'deorderstatus_id'  => 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$deforwardeddeallist = DB::table('deforwardeddeallist')
		->select('*')
		->where('declient_id','>',24061)
		->whereBetween('declient_date', [$request->from, $request->to])
		->where('campaign_id','=',$request->campaign_id)
		->where('depayment_billingpickby','=',$request->user_id)
		->where('deorderstatus_id','=',$request->deorderstatus_id)
		->where('status_id','=',1)
		->groupBy('declient_id')
		->orderBy('declient_id','DESC')
		->get();	
		}else{
		$deforwardeddeallist = DB::table('deforwardeddeallist')
		->select('*')
		->where('declient_id','>',24061)
		->where('depayment_managerpickby','=',$request->user_id)
		->whereBetween('declient_date', [$request->from, $request->to])
		->where('campaign_id','=',$request->campaign_id)
		->where('deorderstatus_id','=',$request->deorderstatus_id)
		->where('status_id','=',1)
		->groupBy('declient_id')
		->orderBy('declient_id','DESC')
		->get();	
		}
		$deforwardeddeallist = $this->paginate($deforwardeddeallist);
		if($deforwardeddeallist){
			return response()->json(['data' => $deforwardeddeallist, 'message' => 'De Pick Deal List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	 public function depickdeal(Request $request){
	 	$validate = Validator::make($request->all(), [ 
	    	'declient_id'  				=> 'required',
	    	'deorderstatus_id' 			=> 'required',
	    	'role_id'		 			=> 'required',
	    	'campaign_id'		 		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->campaign_id == 5) {
		$updateorderstatus = DB::table('depayment')
			->where('declient_id','=',$request->declient_id)
			->update([
			'deorderstatus_id'			=> $request->deorderstatus_id,
			'depayment_nonqualifypickby'=> $request->user_id,
		]); 
		}elseif ($request->role_id == 2) {
		$updateorderstatus = DB::table('depayment')
			->where('declient_id','=',$request->declient_id)
			->update([
			'deorderstatus_id'			=> $request->deorderstatus_id,
			'depayment_billingpickby'	=> $request->user_id,
		]); 
		}else{
		$updateorderstatus = DB::table('depayment')
			->where('declient_id','=',$request->declient_id)
			->update([
			'deorderstatus_id'			=> $request->deorderstatus_id,
			'depayment_managerpickby'	=> $request->user_id,
		]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Deal Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deunpickdeal(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'declient_id'  				=> 'required',
	    	'deorderstatus_id' 			=> 'required',
	    	'role_id'		 			=> 'required',
	    	'campaign_id'		 		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
		$updateorderstatus = DB::table('depayment')
			->where('declient_id','=',$request->declient_id)
			->update([
			'deorderstatus_id'			=> $request->deorderstatus_id,
			'depayment_billingpickby'	=> null,
		]); 
		}elseif ($request->campaign_id == 5) {
		$updateorderstatus = DB::table('depayment')
			->where('declient_id','=',$request->declient_id)
			->update([
			'deorderstatus_id'				=> $request->deorderstatus_id,
			'depayment_nonqualifypickby'	=> null,
		]); 
		}else{
		$updateorderstatus = DB::table('depayment')
			->where('declient_id','=',$request->declient_id)
			->update([
			'deorderstatus_id'			=> $request->deorderstatus_id,
			'depayment_managerpickby'	=> null,
		]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Deal Unpick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dedealdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'declient_id' 		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$dedealdetails = DB::table('dedealdetails')
		->select('*')
		->where('declient_id','=',$request->declient_id)
		->where('status_id','=',1)
		->groupBy('declient_id')
		->orderBy('declient_id','DESC')
		->first();	
		$depaymentdetails = DB::table('depaymentdetails')
		->select('*')
		->where('declient_id','=',$request->declient_id)
		->where('status_id','=',1)
		->get();	
		if($dedealdetails){
			return response()->json(['dealdetails' => $dedealdetails,'paymentdetails' => $depaymentdetails, 'message' => 'De Deal Details'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function declientlist(Request $request){
		$declientlist = DB::table('declientlist')
		->select('*')
		->where('status_id','=',1)
		->groupBy('declient_homephone')
		->orderBy('declient_id','DESC')
		->get();
		$declientlist = $this->paginate($declientlist);
		if($declientlist){
			return response()->json(['declientlist' => $declientlist, 'message' => 'De Client List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function declientdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'declient_id' 		=> 'required',
	    	'campaign_id' 		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$declientdetails = DB::table('declientlist')
		->select('*')
		->where('declient_id','=',$request->declient_id)
		->where('status_id','=',1)
		->first();
		$clientdeallist = DB::table('dedeallist')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('declient_homephone','=',$declientdetails->declient_homephone)
		->where('status_id','=',1)
		->get();
		$clientdeallist = $this->paginate($clientdeallist);
		if($declientdetails){
			return response()->json(['declientdetails' => $declientdetails, 'clientdeallist' => $clientdeallist, 'message' => 'De Client Details'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function agentdealsformanager(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'campaign_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->role_id == 2) {
			$getorderlist = DB::table('dedeallist')
			->select('*')
			->where('declient_id','>',24061)
			->whereBetween('declient_date', [$request->from, $request->to])
			->where('campaign_id','=',$request->campaign_id)
			->where('depayment_billingpickby','=',$request->user_id)
			->whereIn('deorderstatus_id',[7,8,9,10])
			->where('status_id','=',1)
			->groupBy('declient_id')
			->orderBy('declient_id','DESC')
			->get();	
		}else{
			$getorderlist = DB::table('dedeallist')
			->select('*')
			->where('declient_id','>',24061)
			->whereBetween('declient_date', [$request->from, $request->to])
			->where('campaign_id','=',$request->campaign_id)
			->where('depayment_managerpickby','=',$request->user_id)
			->whereIn('deorderstatus_id',[4,5,6,7,8,9,10])
			->where('status_id','=',1)
			->groupBy('declient_id')
			->orderBy('declient_id','DESC')
			->get();	
		}
		$getorderlist = $this->paginate($getorderlist);
		if($getorderlist){
			return response()->json(['data' => $getorderlist, 'message' => 'De Deal List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function updatedeorderstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'declient_id'			=> 'required',
	      'deorderstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus  = DB::table('depayment')
		->where('declient_id','=',$request->declient_id)
		->update([
		'deorderstatus_id' 			=> $request->deorderstatus_id,
		]);
		if($updateorderstatus){
			return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deremovecard(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'depayment_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Payment Id Required", 400);
		}
		$updateorderstatus  = DB::table('depayment')
		->where('depayment_id','=',$request->depayment_id)
		->update([
		'status_id' 			=> 2,
		]);
		if($updateorderstatus){
			return response()->json(['message' => 'Card Removed Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function paginate($items, $perPage = 30, $page = null, $options = []){
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return  new  LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}