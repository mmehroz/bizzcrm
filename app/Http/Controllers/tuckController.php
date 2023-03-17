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

class campaignController extends Controller
{
	public function createcampaign(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'banner' 			=> 'required',
		      'logo'			=> 'required',
		      'website' 		=> 'required',
		      'campaignname'	=> 'required',
		      'email' 			=> 'required',
		      'currency'		=> 'required',
		      'location' 		=> 'required',
		      'campaign_for'	=> 'required',
		      'aboutus'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$validateunique = Validator::make($request->all(), [ 
		      'email' 		=> 'unique:campaign,campaign_email',
		    ]);
	     	if ($validateunique->fails()) {    
				return response()->json("Campaign Email Already Exist", 400);
			}
			$validatebanner = Validator::make($request->all(), [ 
		    	'banner'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatebanner->fails()) {    
				return response()->json("Invalid Image Format", 400);
			}
	        $validatelogo = Validator::make($request->all(), [ 
		    	'logo'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatelogo->fails()) {    
				return response()->json("Invalid Format", 400);
			}
			$userbannername;
        	if ($request->has('banner')) {
            		if( $request->banner->isValid()){
			            $number = rand(1,999);
				        $numb = $number / 7 ;
						$name = "campaignbanner";
				        $extension = $request->banner->extension();
			            $userbannername  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			            $userbannername = $request->banner->move(public_path('campaignbanner/'),$userbannername);
					    $img = Image::make($userbannername)->resize(800,800, function($constraint) {
			                    $constraint->aspectRatio();
			            });
			            $img->save($userbannername);
					    $userbannername = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			        }
            }else{
    	        $userbannername = 'no_image.jpg'; 
	        }
			$userlogoname;
        	if ($request->has('logo')) {
            		if( $request->logo->isValid()){
			            $number = rand(1,999);
				        $numb = $number / 7 ;
						$name = "logo";
				        $extension = $request->logo->extension();
			            $userlogoname  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			            $userlogoname = $request->logo->move(public_path('campaignlogo/'),$userlogoname);
					    $img = Image::make($userlogoname)->resize(800,800, function($constraint) {
			                    $constraint->aspectRatio();
			            });
			            $img->save($userlogoname);
					    $userlogoname = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			        }
            }else{
    	        $userlogoname = 'no_image.jpg'; 
	        }
		$adds[] = array(
		'campaign_banner' 			=> $userbannername,
		'campaign_logo'				=> $userlogoname,
		'campaign_website' 			=> $request->website,
		'campaign_campaignname' 	=> $request->campaignname,
		'campaign_email' 			=> $request->email,
		'currency_id'				=> $request->currency,
		'location_id'		 		=> $request->location,
		'campaign_campaignfor' 		=> $request->campaign_for,
		'campaign_aboutus' 			=> $request->aboutus,
		'status_id'		 			=> 1,
		'created_by'	 			=> $request->user_id,
		'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('campaign')->insert($adds);
		if($save){
			return response()->json(['data' => $adds,'message' => 'Campaign Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatecampaign(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'campaign_id' 	=> 'required',
		      'website' 		=> 'required',
		      'campaignname'	=> 'required',
		      'email' 			=> 'required',
		      'currency'		=> 'required',
		      'location' 		=> 'required',
		      'campaign_for'	=> 'required',
		      'aboutus'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$getcampaignemail = DB::table('campaign')
			->where('campaign_id','=',$request->campaign_id)
			->select('campaign_email')
			->first();
			if ($getcampaignemail->campaign_email != $request->email) {
			$validateunique = Validator::make($request->all(), [ 
		      'email' 		=> 'unique:campaign,campaign_email',
		    ]);
	     	if ($validateunique->fails()) {    
				return response()->json("Campaign Email Already Exist", 400);
			}
			}
			$validatebanner = Validator::make($request->all(), [ 
		    	'banner'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatebanner->fails()) {    
				return response()->json("Invalid Format", 400);
			}
	        $validatelogo = Validator::make($request->all(), [ 
		    	'logo'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatelogo->fails()) {    
				return response()->json("Invalid Format", 400);
			}
			$userbannername;
        	if ($request->has('banner')) {
            		if( $request->banner->isValid()){
			            $number = rand(1,999);
				        $numb = $number / 7 ;
						$name = "banner";
				        $extension = $request->banner->extension();
			            $userbannername  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			            $userbannername = $request->banner->move(public_path('campaignbanner/'),$userbannername);
					    $img = Image::make($userbannername)->resize(800,800, function($constraint) {
			                    $constraint->aspectRatio();
			            });
			            $img->save($userbannername);
					    $userbannername = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			        }
            }else{
    	        $userbannername = 'no_image.jpg'; 
	        }
			$userlogoname;
        	if ($request->has('logo')) {
            		if( $request->logo->isValid()){
			            $number = rand(1,999);
				        $numb = $number / 7 ;
						$name = "logo";
				        $extension = $request->logo->extension();
			            $userlogoname  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			            $userlogoname = $request->logo->move(public_path('campaignlogo/'),$userlogoname);
					    $img = Image::make($userlogoname)->resize(800,800, function($constraint) {
			                    $constraint->aspectRatio();
			            });
			            $img->save($userlogoname);
					    $userlogoname = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			        }
            }else{
    	        $userlogoname = 'no_image.jpg'; 
	        }
		$updatecampaign  = DB::table('campaign')
		->where('campaign_id','=',$request->campaign_id)
		->update([
		'campaign_website' 			=> $request->website,
		'campaign_campaignname' 	=> $request->campaignname,
		'campaign_email' 			=> $request->email,
		'currency_id'				=> $request->currency,
		'location_id'		 		=> $request->location,
		'campaign_campaignfor' 		=> $request->campaign_for,
		'campaign_aboutus' 			=> $request->aboutus,
		'status_id'		 			=> 1,
		'updated_by'	 			=> $request->user_id,
		'updated_at'	 			=> date('Y-m-d h:i:s'),
		]);
		if ($userbannername != 'no_image.jpg') {
			$updatebanner  = DB::table('campaign')
			->where('campaign_id','=',$request->campaign_id)
			->update([
			'campaign_banner'			=> $userbannername,
			]); 
		}
		if ($userlogoname != 'no_image.jpg') {
			$updatelogo  = DB::table('campaign')
			->where('campaign_id','=',$request->campaign_id)
			->update([
			'campaign_logo'			=> $userlogoname,
			]); 
		}
		if($updatecampaign){
			return response()->json(['message' => 'Campaign Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function campaignlist(){
		$getcampaignlist = DB::table('campaign')
		->select('campaign.campaign_id','campaign.campaign_campaignname','campaign.campaign_banner','campaign.campaign_logo','campaign.campaign_website')
		->where('campaign.status_id','=',1)
		->get();
		if($getcampaignlist){
		return response()->json(['data' => $getcampaignlist,'message' => 'Campaign List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function campaigndetails(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'campaign_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getcampaigndetails = DB::table('getcampaigndetails')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->first();
		if($getcampaigndetails){
		return response()->json(['data' => $getcampaigndetails,'message' => 'Campaign Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deletecampaign(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'campaign_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$updatecampaignstatus  = DB::table('campaign')
			->where('campaign_id','=',$request->campaign_id)
			->update([
			'status_id' 		=> 2,
			]); 
		if($updatecampaignstatus){
		return response()->json(['message' => 'Campaign Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}