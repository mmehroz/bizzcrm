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

class crmLoginController extends Controller
{
	public function login(Request $request){
	    $validate = Validator::make($request->all(), [ 
		      'email' 		=> 'required',
		      'password'	=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Enter Credentials To Signin", 400);
			}
		$getprofileinfo = DB::table('user')
			->select('*')
			->where('user_email','=',$request->email)
			->where('user_password','=',$request->password)
			->where('status_id','=',1)
			->first();
			if($getprofileinfo){
			$updateuser  = DB::table('user')
			->where('user_id','=',$getprofileinfo->user_id)
			->update([
			'user_loginstatus' 		=> "Online",
			]); 
			$getinfo = DB::table('loginuserinfo')
			->select('*')
			->where('user_id','=',$getprofileinfo->user_id)
			->where('status_id','=',1)
			->first();
				return response()->json(['data' => $getinfo,'message' => 'Login Successfully'],200);
			}else{
				return response()->json("Invalid Email Or Password", 400);
			}
	}
	public function role(){
		$getroles = DB::table('role')
		->select('role.role_id','role.role_name')
		->where('role.status_id','=',1)
		->get();
		return response()->json(['data' => $getroles,'message' => 'CRM Role'],200);
	}
	public function locationandcurrency(){
		$getlocation = DB::table('location')
		->select('location.location_id','location.location_name')
		->where('location.status_id','=',1)
		->get();
		
		$getcurrency = DB::table('currency')
		->select('currency.currency_id','currency.currency_name')
		->where('currency.status_id','=',1)
		->get();
		return response()->json(['currency' => $getcurrency,'location' => $getlocation,'message' => 'Location And Currency'],200);
	}
	public function logout(Request $request){
		$logoutuser  = DB::table('user')
			->where('user_id','=',$request->user_id)
			->update([
			'user_loginstatus' 		=> "Offline",
		]); 
		return response()->json(['message' => 'Logout Successfully'],200);
	}
}