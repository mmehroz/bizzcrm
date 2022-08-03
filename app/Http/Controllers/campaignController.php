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
	public function campaigntype(){
		$getcampaigntype = DB::table('campaigntype')
		->select('*')
		->where('status_id','=',1)
		->get();
		if($getcampaigntype){
			return response()->json(['data' => $getcampaigntype,'message' => 'Campaign Type'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function createcampaign(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'banner' 				=> 'required',
		      'logo'				=> 'required',
		      'website' 			=> 'required',
		      'campaignname'		=> 'required',
		      'email' 				=> 'required',
		      'currency'			=> 'required',
		      'location' 			=> 'required',
		      'campaign_for'		=> 'required',
		      'aboutus'				=> 'required',
		      'campaigntype'		=> 'required',
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
		'campaigntype_id' 			=> $request->campaigntype,
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
		      'campaign_id' 		=> 'required',
		      'website' 			=> 'required',
		      'campaignname'		=> 'required',
		      'email' 				=> 'required',
		      'currency'			=> 'required',
		      'location' 			=> 'required',
		      'campaign_for'		=> 'required',
		      'aboutus'				=> 'required',
		      'campaigntype'		=> 'required',
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
		'campaigntype_id' 			=> $request->campaigntype,
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
		->select('campaign.campaign_id','campaigntype_id','campaign.campaign_campaignname','campaign.campaign_banner','campaign.campaign_logo','campaign.campaign_website')
		->where('campaign.status_id','=',1)
		->where('campaign.campaign_id','!=',0)
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
	public function stateslist(Request $request){
		$getstates = DB::table('state')
		->select('*')
		->where('status_id','=',1)
		->get();
		if($getstates){
			return response()->json(['data' => $getstates,'message' => 'State List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'State Not Found'],200);
		}
	}
	public function addpost(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'post_title'	=> 'required',
		      'campaign_id'	=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$validatebanner = Validator::make($request->all(), [ 
		    	'post_image'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatebanner->fails()) {    
				return response()->json("Invalid Image Format Or Size Too Large", 400);
			}
	      	$userpost_imagename;
        	if ($request->has('post_image')) {
            		if( $request->post_image->isValid()){
			            $number = rand(1,999);
				        $numb = $number / 7 ;
						$name = "post_image";
				        $extension = $request->post_image->extension();
			            $userpost_imagename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			            $userpost_imagename = $request->post_image->move(public_path('campaignpost_image/'),$userpost_imagename);
					    $img = Image::make($userpost_imagename)->resize(800,800, function($constraint) {
			                    $constraint->aspectRatio();
			            });
			            $img->save($userpost_imagename);
					    $userpost_imagename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			        }
            }else{
    	        $userpost_imagename = 'no_image.jpg'; 
	        }
			$adds[] = array(
			'post_image' 	=> $userpost_imagename,
			'post_title' 	=> $request->post_title,
			'campaign_id'	=> $request->campaign_id,
			'status_id'		=> 1,
			'created_by'	=> $request->user_id,
			'created_at'	=> date('Y-m-d h:i:s'),
			);
			$save = DB::table('post')->insert($adds);
		if($save){
			return response()->json(['message' => 'Post Submited Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function showpost(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'campaign_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getpost = DB::table('post')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('post_id','DESC')
		->first();
		if($getpost){
			return response()->json(['data' => $getpost, 'message' => 'Recent Post'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray, 'message' => "No Post Available"], 400);
		}
	}
	public function weekdates(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'date'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Date Field Is Required", 400);
		}
		$getyearandmonth = explode('-', $request->date);
		$year = $getyearandmonth[0];
		$month = $getyearandmonth[1];
		$date = $year."-".$month."-01";
		$firstdate = date("$year-$month-01");
		$lastdate = date("Y-m-t", strtotime($firstdate));
		$dt= strtotime($date);
		$currdt=$dt;
		$nextmonth=strtotime($date."+1 month");
		$i=0;
		do 
		{
		    $weekday= date("w",$currdt);
		    $nextday=7-$weekday;
		    $endday=abs($weekday-6);
		    $startarr[$i]=$currdt;
		    $endarr[$i]=strtotime(date("Y-m-d",$currdt)."+$endday day");
		    $currdt=strtotime(date("Y-m-d",$endarr[$i])."+1 day");
		    $weekdate[$i]['startdate'] = date("Y-m-d",$startarr[$i]);
		    if (date("Y-m-d",$endarr[$i]) == date("Y-$month-d",$endarr[$i])) {
		    $weekdate[$i]['enddate'] = date("Y-m-d",$endarr[$i]);
		    }else{
		    $weekdate[$i]['enddate'] = $lastdate;
		    }
		    $i++;
		 	  		     
		}while($endarr[$i-1]<$nextmonth);
		return response()->json(['weekdate' => $weekdate, 'message' => 'Week Dates'],200);
	}
	public function updatepaymentduedate(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'campaign_id' 			=> 'required',
	      'campaign_paymentduedate'	=> 'required',
		]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$update  = DB::table('campaign')
		->where('campaign_id','=',$request->campaign_id)
		->update([
		'campaign_paymentduedate'	=> $request->campaign_paymentduedate,
		]);
		if($update){
			return response()->json(['message' => 'Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}