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

class cpacrmController extends Controller
{
	public function cpamanagerforwardedorderlist(Request $request){
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
		->where('dmeclient_cpamanagerpickby','=',null)
		->orderBy('dmeclient_id','DESC')
		->get();
		$getorderlist = $this->paginate($getorderlist);
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function cpamanagerpickorderlist(Request $request){
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
		->where('orderstatus_id','=',3)
		->orderBy('dmeclient_id','DESC')
		->get();
		}else{
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->where('dmeclient_cpamanagerpickby','=',$request->user_id)
		->where('cpaorderstatus','=',null)
		->orderBy('dmeclient_id','DESC')
		->get();
		}
		$getorderlist = $this->paginate($getorderlist);
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	 public function unpickcpaorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'dmeclient_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if($request->role_id == 3){
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_cpamanagerpickby'	=> null,
				'dmeclient_cpalastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Unpick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function pickcpaorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'			=> 'required',
	      'dmeclient_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateorderstatus;
		if($request->role_id == 3){
			$updateorderstatus = DB::table('dmeclient')
				->where('dmeclient_id','=',$request->dmeclient_id)
				->update([
				'dmeclient_cpamanagerpickby'	=> $request->user_id,
				'dmeclient_cpalastupdateddate'	=> date('Y-m-d'),
			]); 
		}
		if($updateorderstatus){
		return response()->json(['message' => 'Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatecpaorderstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmeclient_id'			=> 'required',
	      'cpaorderstatus'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		if ($request->cpaorderstatus == 3) {
		$validate = Validator::make($request->all(), [ 
	      'dmeotherdetails_cpacomment'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Comment Required", 400);
		}
		$updateorderstatus  = DB::table('dmeotherdetails')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->update([
		'dmeotherdetails_cpacomment' 	=> $request->dmeotherdetails_cpacomment,
		]); 	
		}
		$updateorderstatus  = DB::table('dmeclient')
		->where('dmeclient_id','=',$request->dmeclient_id )
		->update([
		'cpaorderstatus' 				=> $request->cpaorderstatus,
		'dmeclient_cpalastupdateddate'	=> date('Y-m-d'),
		]); 
		if($updateorderstatus){
			return response()->json(['message' => 'Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function cpastatuswiseorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'to'				=> 'required',
	      'from'			=> 'required',
	      'cpaorderstatus'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$from = $request->from;
		$to = $request->to;
		$getorderlist = DB::table('dmeorderdetails')
		->select('*')
		->where('status_id','=',1)
		->whereBetween('dmeclient_date', [$from, $to])
		->where('cpaorderstatus','=',$request->cpaorderstatus)
		->orderBy('dmeclient_id','DESC')
		->get();
		$getorderlist = $this->paginate($getorderlist);
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Order List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Order Not Found'],200);
		}
	}
	public function paginate($items, $perPage = 30, $page = null, $options = []){
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return  new  LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}