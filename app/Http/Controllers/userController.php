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

class userController extends Controller
{
	public function createuser(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'name' 			=> 'required',
		      'email'			=> 'required',
		      'officenumberext' => 'required',
		      'phonenumber'		=> 'required',
		      'username' 		=> 'required',
		      'target'			=> 'required',
		      'password' 		=> 'required',
		      'campaign_id'		=> 'required',
		      'role_id'			=> 'required',
		      // 'user_type'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$validateunique = Validator::make($request->all(), [ 
		      'email' 		=> 'unique:user,user_email',
		    ]);
	     	if ($validateunique->fails()) {    
				return response()->json("User Email Already Exist", 400);
			}
			$validatepicture = Validator::make($request->all(), [ 
		    	'picture'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatepicture->fails()) {    
				return response()->json("Invalid Format", 400);
			}
			$userpicturename;
        	if ($request->has('picture')) {
            		if( $request->picture->isValid()){
			            $number = rand(1,999);
				        $numb = $number / 7 ;
						$name = "userpicture";
				        $extension = $request->picture->extension();
			            $userpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			            $userpicturename = $request->picture->move(public_path('userpicture/'),$userpicturename);
					    $img = Image::make($userpicturename)->resize(800,800, function($constraint) {
			                    $constraint->aspectRatio();
			            });
			            $img->save($userpicturename);
					    $userpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			        }
            }else{
    	        $userpicturename = 'no_image.jpg'; 
	        }
		$adds[] = array(
		'user_name' 			=> $request->name,
		'user_email'			=> $request->email,
		'user_officenumberext' 	=> $request->officenumberext,
		'user_phonenumber' 		=> $request->phonenumber,
		'user_username' 		=> $request->username,
		'user_target'			=> $request->target,
		'user_targetmonth'		=> date('n'),
		'user_password' 		=> $request->password,
		'user_picture'			=> $userpicturename,
		'user_loginstatus' 		=> "Offline",
		'user_type'				=> $request->user_type,
		'campaign_id' 			=> $request->campaign_id,
		'role_id' 				=> $request->role_id,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('user')->insert($adds);
		$lastid = DB::getPdo()->lastInsertId();
		$target[] = array(
		'target_month'	=> date('n'),
		'target_amount'	=> $request->target,
		'user_id' 		=> $lastid,
		'status_id' 	=> 1,
		'created_by'	=> $request->user_id,
		'created_at'	=> date('Y-m-d h:i:s'),
		);
		DB::table('target')->insert($target);
		if($save){
			return response()->json(['data' => $adds,'message' => 'User Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateuser(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'edituser_id'		=> 'required',
		      'name' 			=> 'required',
		      'email'			=> 'required',
		      'officenumberext' => 'required',
		      'phonenumber'		=> 'required',
		      'username' 		=> 'required',
		      'target'			=> 'required',
		      'role_id'			=> 'required',
		      // 'user_type'		=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$getuseremail = DB::table('user')
			->select('user.user_email')
			->where('user.user_id','=',$request->edituser_id)
			->first();
			if ($getuseremail->user_email != $request->email) {
			$validateunique = Validator::make($request->all(), [ 
		      'email' 		=> 'unique:user,user_email',
		    ]);
	     	if ($validateunique->fails()) {    
				return response()->json("User Email Already Exist", 400);
			}
			}
			$userpicturename;
        	if ($request->has('picture')) {
			$validatepicture = Validator::make($request->all(), [ 
		    	'picture'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatepicture->fails()) {    
				return response()->json("Invalid Format", 400);
			}
            		if( $request->picture->isValid()){
			            $number = rand(1,999);
				        $numb = $number / 7 ;
						$name = "userpicture";
				        $extension = $request->picture->extension();
			            $userpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			            $userpicturename = $request->picture->move(public_path('userpicture/'),$userpicturename);
					    $img = Image::make($userpicturename)->resize(800,800, function($constraint) {
			                    $constraint->aspectRatio();
			            });
			            $img->save($userpicturename);
					    $userpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			        }
            }else{
    	        $userpicturename = 'no_image.jpg'; 
	        }
	    $updateuser  = DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'user_name' 			=> $request->name,
			'user_email'			=> $request->email,
			'user_officenumberext' 	=> $request->officenumberext,
			'user_phonenumber' 		=> $request->phonenumber,
			'user_username' 		=> $request->username,
			'user_type'				=> $request->user_type,
			'role_id' 				=> $request->role_id,
			'status_id'		 		=> 1,
			'updated_by'	 		=> $request->user_id,
			'updated_at'	 		=> date('Y-m-d h:i:s'),
		]); 
		if ($userpicturename != 'no_image.jpg') {
			DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'user_picture'			=> $userpicturename,
			]); 
		}
		if ($request->password != "") {
			DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'user_password' 		=> $request->password,
			]); 
		}
		$getcurrentmonth = date('n');
		$gettargetmonth = DB::table('user')
			->select('user.user_targetmonth')
			->where('user.user_id','=',$request->edituser_id)
			->where('user.status_id','=',1)
			->first();
		if ($gettargetmonth->user_targetmonth == $getcurrentmonth) {
			$updateusertarget  = DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'user_target' 		=> $request->target,
			]); 	
			$updatetarget  = DB::table('target')
			->where('user_id','=',$request->edituser_id)
			->where('target_month','=',$getcurrentmonth)
			->update([
			'target_amount' 		=> $request->target,
			'updated_by' 			=> $request->edituser_id,
			'updated_at' 			=> date('Y-m-d h:i:s'),
			]);
		}else{
			$updateusernewtarget  = DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'user_target' 		=> $request->target,
			'user_targetmonth' 	=> $getcurrentmonth,
			]); 	
		$target[] = array(
		'target_month'	=> $getcurrentmonth,
		'target_amount'	=> $request->target,
		'user_id' 		=> $request->edituser_id,
		'status_id' 	=> 1,
		'created_by'	=> $request->user_id,
		'created_at'	=> date('Y-m-d h:i:s'),
		);
		DB::table('target')->insert($target);
		}
		if($updateuser){
			return response()->json(['message' => 'User Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function userlist(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'campaign_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getseniormanagerlist = DB::table('user')
		->select('user_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture')
		->where('role_id','=',9)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getmanagerlist = DB::table('user')
		->select('user_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture')
		->where('role_id','=',3)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getbdmanagerlist = DB::table('user')
		->select('user_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture')
		->where('role_id','=',7)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getbillinglist = DB::table('user')
		->select('user_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture')
		->where('role_id','=',2)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getagentlist = DB::table('user')
		->select('user_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture')
		->where('role_id','=',4)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getdesignerlist = DB::table('user')
		->select('user_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture')
		->where('role_id','=',5)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getdegitizerlist = DB::table('user')
		->select('user_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture')
		->where('role_id','=',6)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getdoctorchaselist = DB::table('user')
		->select('user_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture')
		->where('role_id','=',10)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getleadlist = DB::table('user')
		->select('user_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture')
		->where('role_id','=',12)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		if($getmanagerlist || $getagentlist || $getdesignerlist || $getdegitizerlist || $getseniormanagerlist || $getbdmanagerlist || $getdoctorchaselist || $getleadlist){
		return response()->json(['seniormanager' => $getseniormanagerlist, 'manager' => $getmanagerlist, 'bdmanager' => $getbdmanagerlist, 'billing' => $getbillinglist, 'agent' => $getagentlist, 'designer' => $getdesignerlist,'digitizer' => $getdegitizerlist,'doctorchase' => $getdoctorchaselist,'leads' => $getleadlist, 'message' => 'User List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function userdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'edituser_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getuserdetails = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->edituser_id)
		->where('status_id','=',1)
		->first();
		if($getuserdetails){
		return response()->json(['data' => $getuserdetails,'message' => 'User Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deleteuser(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'edituser_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$updateuserstatus  = DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'status_id' 		=> 2,
			]); 
		if($updateuserstatus){
		return response()->json(['message' => 'User Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function uploadcoverpicture(Request $request){
		$validatepicture = Validator::make($request->all(), [ 
	    	'coverpicture'=>'mimes:jpeg,bmp,png,jpg|max:10120',
	    ]);
		if ($validatepicture->fails()) {    
			return response()->json("Invalid Format", 400);
		}
		$usercoverpicturename;
    	if ($request->has('coverpicture')) {
        		if( $request->coverpicture->isValid()){
		            $number = rand(1,999);
			        $numb = $number / 7 ;
					$name = "userpicture";
			        $extension = $request->coverpicture->extension();
		            $usercoverpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		            $usercoverpicturename = $request->coverpicture->move(public_path('coverpicture/'),$usercoverpicturename);
				    $img = Image::make($usercoverpicturename)->resize(800,800, function($constraint) {
		                    $constraint->aspectRatio();
		            });
		            $img->save($usercoverpicturename);
				    $usercoverpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		        }
        }else{
	        $usercoverpicturename = 'no_image.jpg'; 
        }
		$updatecoverpicture = DB::table('user')
			->where('user_id','=',$request->user_id)
			->update([
			'user_coverpicture'		=> $usercoverpicturename,
		]); 
		if($updatecoverpicture){
			return response()->json(['data' => $usercoverpicturename,'message' => 'User Cover Picture Uploaded Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}