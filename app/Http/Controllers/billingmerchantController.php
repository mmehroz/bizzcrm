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

class billingmerchantController extends Controller
{
	public function addbillingmerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
            'billingmerchant_logo'              => 'required',
            'billingmerchant_title' 			=> 'required',
            'billingmerchant_email'		        => 'required',
            'billingmerchant_website' 			=> 'required',
            'billingmerchant_openingbalance'	=> 'required',
            'billingmerchant_fee' 			    => 'required',
            'billingmerchant_otherinfo'		    => 'required',
		]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
        $validateunique = Validator::make($request->all(), [ 
            'billingmerchant_email' => 'unique:billingmerchant,billingmerchant_email',
        ]);
        if ($validateunique->fails()) {    
            return response()->json($validate->errors(), 400);
        }
        $validatelogo = Validator::make($request->all(), [ 
            'billingmerchant_logo' => 'mimes:jpeg,bmp,png,jpg|max:5120',
        ]);
        if ($validatelogo->fails()) {    
            return response()->json("Invalid Format", 400);
        }
        $billingmerchantlogo;
        if ($request->has('billingmerchant_logo')) {
            if( $request->billingmerchant_logo->isValid()){
                $number = rand(1,999);
                $numb = $number / 7 ;
                $name = "logo";
                $extension = $request->billingmerchant_logo->extension();
                $billingmerchantlogo  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
                $billingmerchantlogo = $request->billingmerchant_logo->move(public_path('billingmerchantlogo/'),$billingmerchantlogo);
                $img = Image::make($billingmerchantlogo)->resize(800,800, function($constraint) {
                        $constraint->aspectRatio();
                });
                $img->save($billingmerchantlogo);
                $billingmerchantlogo = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
            }else{
                return response()->json("Invalid Format", 400);
            }
        }
        $adds[] = array(
		'billingmerchant_logo'		        => $billingmerchantlogo,
		'billingmerchant_title' 	        => $request->billingmerchant_title,
		'billingmerchant_email' 	        => $request->billingmerchant_email,
		'billingmerchant_website' 	        => $request->billingmerchant_website,
		'billingmerchant_openingbalance'	=> $request->billingmerchant_openingbalance,
		'billingmerchant_fee'		 		=> $request->billingmerchant_fee,
		'billingmerchant_otherinfo' 		=> $request->billingmerchant_otherinfo,
        'campaign_id'                		=> $request->campaign_id,
		'status_id'		 			        => 1,
		'created_by'	 			        => $request->user_id,
		'created_at'	 			        => date('Y-m-d h:i:s'),
		);
		$save = DB::table('billingmerchant')->insert($adds);
		if($save){
			return response()->json(['data' => $adds,'message' => 'Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatebillingmerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
            'billingmerchant_title' 			=> 'required',
            'billingmerchant_email'		        => 'required',
            'billingmerchant_website' 			=> 'required',
            'billingmerchant_openingbalance'	=> 'required',
            'billingmerchant_fee' 			    => 'required',
            'billingmerchant_otherinfo'		    => 'required',
		]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
        $getemail = DB::table('billingmerchant')
        ->where('billingmerchant_id','=',$request->billingmerchant_id)
        ->select('billingmerchant_email')
        ->first();
        if ($getemail->billingmerchant_email != $request->billingmerchant_email) {
            $validateunique = Validator::make($request->all(), [ 
                'billingmerchant_email' => 'unique:billingmerchant,billingmerchant_email',
            ]);
            if ($validateunique->fails()) {    
                return response()->json("Email Already Exist", 400);
            }
        }
        if(isset($request->billingmerchant_logo)){
            $validatelogo = Validator::make($request->all(), [ 
                'billingmerchant_logo'=>'mimes:jpeg,bmp,png,jpg|max:5120|required',
            ]);
            if ($validatelogo->fails()) {    
                return response()->json("Invalid Format", 400);
            }
        $billingmerchantlogo;
        if( $request->billingmerchant_logo->isValid()){
            $number = rand(1,999);
            $numb = $number / 7 ;
            $name = "logo";
            $extension = $request->billingmerchant_logo->extension();
            $billingmerchantlogo  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
            $billingmerchantlogo = $request->billingmerchant_logo->move(public_path('billingmerchantlogo/'),$billingmerchantlogo);
            $img = Image::make($billingmerchantlogo)->resize(800,800, function($constraint) {
                    $constraint->aspectRatio();
            });
            $img->save($billingmerchantlogo);
            $billingmerchantlogo = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
            DB::table('billingmerchant')
			->where('billingmerchant_id ','=',$request->billingmerchant_id )
			->update([
			'billingmerchant_logo'			=> $billingmerchantlogo,
			]); 
        }else{
            return response()->json("Invalid Format", 400);
        }
        }
        $update  = DB::table('billingmerchant')
		->where('billingmerchant_id','=',$request->billingmerchant_id)
		->update([
            'billingmerchant_title' 			=> $request->billingmerchant_title,
            'billingmerchant_email' 	        => $request->billingmerchant_email,
            'billingmerchant_website' 			=> $request->billingmerchant_website,
            'billingmerchant_openingbalance'	=> $request->billingmerchant_openingbalance,
            'billingmerchant_fee'		 		=> $request->billingmerchant_fee,
            'billingmerchant_otherinfo' 		=> $request->billingmerchant_otherinfo,
            'updated_by'	 			        => $request->user_id,
            'updated_at'	 			        => date('Y-m-d h:i:s'),
		]);
		if($update){
			return response()->json(['message' => 'Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function billingmerchantlist(){
		$getlist = DB::table('billingmerchant')
		->select('*')
		->where('status_id','=',1)
		->get();
		if($getlist){
		    return response()->json(['data' => $getlist,'message' => 'Billing Merchant List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function billingmerchantdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
            'billingmerchant_id'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
		$getdetails = DB::table('billingmerchant')
		->select('*')
		->where('billingmerchant_id','=',$request->billingmerchant_id)
		->where('status_id','=',1)
		->first();
		if($getdetails){
		    return response()->json(['data' => $getdetails,'message' => 'Billing Merchant Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deletebillingmerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
            'billingmerchant_id'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
		$delete  = DB::table('billingmerchant')
        ->where('billingmerchant_id','=',$request->billingmerchant_id)
        ->update([
            'status_id'   => 2,
        ]); 
		if($delete){
		    return response()->json(['message' => 'Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}