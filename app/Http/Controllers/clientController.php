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

class clientController extends Controller
{
	public function createclient(Request $request){
		$getcampaignid = DB::table('user')
			->select('campaign_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->first();
		if (isset($getcampaignid)) {
			$getuseremail = DB::table('client')
			->select('client_email')
			->where('client_email','=',$request->email)
			->where('status_id','=',1)
			->where('campaign_id','=',$getcampaignid->campaign_id)
			->first();
		}
		if (isset($getuseremail)) {
			return response()->json("Client Email Already Exist", 400);
		}
		$validate = Validator::make($request->all(), [ 
		      'companyname' 			=> 'required',
		      'contactperson'			=> 'required',
		      'address' 				=> 'required',
		      'officenumber'			=> 'required',
		      'alternateofficenumber' 	=> 'required',
		      'twitterid'				=> 'required',
		      'facebookid' 				=> 'required',
		      'instagramid'				=> 'required',
		      'state'					=> 'required',
		      'city' 					=> 'required',
		      'country'					=> 'required',
		      'timezone' 				=> 'required',
		      'email'					=> 'required',
		      'alternateemail' 			=> 'required',
		      'website'					=> 'required',
		      'companyindustry' 		=> 'required',
		      'designation'				=> 'required',
		      'companydescription'		=> 'required',
		      'zipcode'					=> 'required',
		      'totalrevenue'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$adds[] = array(
		'client_companyname' 			=> $request->companyname,
		'client_contactperson'			=> $request->contactperson,
		'client_address' 				=> $request->address,
		'client_officenumber' 			=> $request->officenumber,
		'client_alternateofficenumber' 	=> $request->alternateofficenumber,
		'client_twitterid'				=> $request->twitterid,
		'client_facebookid' 			=> $request->facebookid,
		'client_instagramid' 			=> $request->instagramid,
		'client_state' 					=> $request->state,
		'client_city' 					=> $request->city,
		'location_id'					=> $request->country,
		'client_timezone' 				=> $request->timezone,
		'client_email' 					=> $request->email,
		'client_alternateemail' 		=> $request->alternateemail,
		'client_website'				=> $request->website,
		'client_companyindustry' 		=> $request->companyindustry,
		'client_designation' 			=> $request->designation,
		'client_companydecription' 		=> $request->companydescription,
		'client_zipcode' 				=> $request->zipcode,
		'client_totalrevenue' 			=> $request->totalrevenue,
		'campaign_id'		 			=> $getcampaignid->campaign_id,
		'status_id'		 				=> 1,
		'created_by'	 				=> $request->user_id,
		'created_at'	 				=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('client')->insert($adds);
		$client_id = DB::getPdo()->lastInsertId();
		if($save){
			return response()->json(['data' => $client_id,'alldata' => $adds,'message' => 'Client Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateclient(Request $request){
		$getclientemail = DB::table('client')
		->select('client_email')
		->where('client_id','=',$request->client_id)
		->first();
		if ($getclientemail->client_email != $request->email) {
		$validateunique = Validator::make($request->all(), [ 
		      'email' 		=> 'unique:client,client_email',
		    ]);
	     	if ($validateunique->fails()) {    
				return response()->json("Client Email Already Exist", 400);
			}
		}
		$validate = Validator::make($request->all(), [ 
		      'companyname' 			=> 'required',
		      'contactperson'			=> 'required',
		      'address' 				=> 'required',
		      'officenumber'			=> 'required',
		      'alternateofficenumber' 	=> 'required',
		      'twitterid'				=> 'required',
		      'facebookid' 				=> 'required',
		      'instagramid'				=> 'required',
		      'state'					=> 'required',
		      'city' 					=> 'required',
		    //   'country'					=> 'required',
		      'timezone' 				=> 'required',
		      'email'					=> 'required',
		      'alternateemail' 			=> 'required',
		      'website'					=> 'required',
		      'companyindustry' 		=> 'required',
		      'designation'				=> 'required',
		      'companydescription'		=> 'required',
		      'zipcode'					=> 'required',
		      'totalrevenue'			=> 'required',
		      'client_id'				=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json($validate->errors(), 400);
			}
		$updateclient  = DB::table('client')
		->where('client_id','=',$request->client_id)
		->update([
		'client_companyname' 			=> $request->companyname,
		'client_contactperson'			=> $request->contactperson,
		'client_address' 				=> $request->address,
		'client_officenumber' 			=> $request->officenumber,
		'client_alternateofficenumber' 	=> $request->alternateofficenumber,
		'client_twitterid'				=> $request->twitterid,
		'client_facebookid' 			=> $request->facebookid,
		'client_instagramid' 			=> $request->instagramid,
		'client_state' 					=> $request->state,
		'client_city' 					=> $request->city,
		// 'location_id'					=> $request->country,
		'client_timezone' 				=> $request->timezone,
		'client_email' 					=> $request->email,
		'client_alternateemail' 		=> $request->alternateemail,
		'client_website'				=> $request->website,
		'client_companyindustry' 		=> $request->companyindustry,
		'client_designation' 			=> $request->designation,
		'client_companydecription' 		=> $request->companydescription,
		'client_zipcode' 				=> $request->zipcode,
		'client_totalrevenue' 			=> $request->totalrevenue,
		'status_id'		 				=> 1,
		'updated_by'	 				=> $request->user_id,
		'updated_at'	 				=> date('Y-m-d h:i:s'),
		]);
		if($updateclient){
			return response()->json(['message' => 'Client Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function clientlist(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'role_id'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getpreviousmonth = date('Y-m-d', strtotime('-2 months'));
		$getclientlist;
		if ($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 9 || $request->user_id == 131 || $request->user_id == 4) {
			$getclientlist = DB::table('getclientlist')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('status_id','=',1)
			->get();
		}else{
			$getclientlist = DB::table('getclientlist')
			->select('*')
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->user_id)
			->where('status_id','=',1)
			->get();
		}
		// $clientdata = array();
		// $index = 0;
		// foreach ($getclientlist as $getclientlist) {
		// 	$getclientlist->unpaidamount = DB::table('order')
		// 	->select('order_amountquoted')
		// 	->where('client_id','=',$getclientlist->client_id)
		// 	->where('status_id','=',1)
		// 	->where('campaign_id','=',$request->campaign_id)
		// 	->whereIn('orderstatus_id',[7,8,9,10,17])
		// 	->where('order_date','<', $getpreviousmonth)
		// 	->sum('order_amountquoted');
		// 	$clientdata[$index] = $getclientlist;
		// 	$index++;
		// }
		if($getclientlist){
		return response()->json(['data' => $getclientlist,'message' => 'Client List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function clientdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'client_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getclientdetails = DB::table('getclientdetails')
		->select('*')
		->where('client_id','=',$request->client_id)
		->where('status_id','=',1)
		->first();
		if($getclientdetails){
		return response()->json(['data' => $getclientdetails,'message' => 'Client Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deleteclient(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'client_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$updateclientstatus  = DB::table('client')
			->where('client_id','=',$request->client_id)
			->update([
			'status_id' 		=> 2,
			]); 
		if($updateclientstatus){
		return response()->json(['message' => 'Client Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function clientprofile(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'client_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getclientprofile = DB::table('getclientdetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('client_id','=',$request->client_id)
		->where('status_id','=',1)
		->first();

		$getclientunpaid = DB::table('order')
		->select('order_amountquoted')
		->where('campaign_id','=',$request->campaign_id)
		->where('client_id','=',$request->client_id)
		->whereNotIn('orderstatus_id',[11,12,18])
		->where('status_id','=',1)
		->sum('order_amountquoted');

		$getclientdeals = DB::table('getorderdetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('client_id','=',$request->client_id)
		->where('status_id','=',1)
		->orderBy('order_id','DESC')
		->limit(50)
		->get();

		$getcreatorid = DB::table('order')
		->select('created_by')
		->where('client_id','=',$request->client_id)
		->first();
		if (isset($getcreatorid)) {
		if ($getcreatorid->created_by != $request->user_id && $request->user_id == 131) {
			$emptyaray = array();
			return response()->json(['profile' => $getclientprofile,'deals' => $emptyaray, 'unpaidamount' => $getclientunpaid, 'message' => 'Client Complete Profile'],200);
		}}
		if($getclientprofile){
			return response()->json(['profile' => $getclientprofile,'deals' => $getclientdeals, 'unpaidamount' => $getclientunpaid, 'message' => 'Client Complete Profile'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function csvToArray($filename = '', $delimiter = ','){
	    if (!file_exists($filename) || !is_readable($filename))
	        return false;
		$header = null;
	    $data = array();
	    if (($handle = fopen($filename, 'r')) !== false)
	    {
	        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
	        {
	            if (!$header)
	                $header = $row;
	            else
	                $data[] = array_combine($header, $row);
	        }
	        fclose($handle);
	    }
		return $data;
	}
	public function importrawclient(Request $request){
	    $validate = Validator::make($request->all(), [ 
		      'file'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Select File To Upload", 400);
		}
	    $file = $request->file;
	    $customerArr = $this->csvToArray($file);
		for ($i = 0; $i < count($customerArr); $i ++)
	    {
	    	$save = DB::table('rawclient')->insert($customerArr[$i]);
	    }
	    if ($save) {
	    	return response()->json(['message' => 'Upload Successfully'],200);
	    }else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function newlead(Request $request){
		$getnewlead = DB::table('newlead')
		->select('*')
		->get();
		return response()->json(['data' => $getnewlead,'message' => 'New Leads'],200);
	}
	public function transferclient(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'client_id'	=> 'required',
	      'id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateclientstatus  = DB::table('client')
			->where('client_id','=',$request->client_id)
			->update([
			'created_by' 		=> $request->id,
			]); 
		return response()->json(['message' => 'Client Transfer Successfully'],200);
	}
	public function lockclient(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'client_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateclientstatus  = DB::table('client')
			->where('client_id','=',$request->client_id)
			->update([
			'client_islock' 		=> 1,
			'client_lockcomment' 	=> $request->client_lockcomment,
			]); 
		if($updateclientstatus){
		return response()->json(['message' => 'Client Locked Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function unlockclient(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'client_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$updateclientstatus  = DB::table('client')
			->where('client_id','=',$request->client_id)
			->update([
			'client_islock' 		=> 0,
			]); 
		if($updateclientstatus){
		return response()->json(['message' => 'Client Unlocked Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}