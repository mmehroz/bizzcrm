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

class denonqualifyController extends Controller
{
	public function deforwardednonqualifieddeal(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'campaign_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getorderlist = DB::table('dedeallist')
		->select('*')
		->where('declient_id','>',24061)
		->whereBetween('declient_date', [$request->from, $request->to])
		->where('campaign_id','=',$request->campaign_id)
		->where('deorderstatus_id','=',11)
		->where('status_id','=',1)
		->groupBy('declient_id')
		->orderBy('declient_id','DESC')
		->get();	
		$getorderlist = $this->paginate($getorderlist);
		if($getorderlist){
			return response()->json(['data' => $getorderlist, 'message' => 'De Deal List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function depickedstatuswisedeal(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'campaign_id'  		=> 'required',
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
		->where('depayment_nonqualifypickby','=',$request->user_id)
		->where('deorderstatus_id','=',$request->deorderstatus_id)
		->where('status_id','=',1)
		->groupBy('declient_id')
		->orderBy('declient_id','DESC')
		->get();	
		$getorderlist = $this->paginate($getorderlist);
		if($getorderlist){
			return response()->json(['data' => $getorderlist, 'message' => 'De Deal List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function paginate($items, $perPage = 30, $page = null, $options = []){
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return  new  LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}