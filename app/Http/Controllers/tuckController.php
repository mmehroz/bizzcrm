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

class tuckController extends Controller
{
	public function tuckcategorylist(){
	$getcategorylist = DB::table('tuckcategory')
		->select('*')
		->where('status_id','=',1)
		->get();
		if($categorylist){
			return response()->json(['categories' => $categorylist],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function tuckproductlist($id){
		$validate = Validator::make($request->all(), [ 
		      'category_id'			=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
		$getproductlist = DB::table('tuckproduct')
		->select('*')
		->where('status_id','=',1)
		->where('category_id','=',$request->category_id)
		->get();
		if($getproductlist){
			return response()->json(['product' => $getproductlist],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function tuckcreateorder(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'category_id'				=> 'required',
		      'product_id'				=> 'required',
		      'order_productprice'		=> 'required',
		      'order_productquantity'	=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json("Fields Required", 400);
			}
			$addproduct[] = array(
				'category_id' 		=> $getids[0],
				'product_id'			=> $getids[1],
				'order_productprice' 	=> $getproductpriceandqty->product_price,
				'order_productquantity' => $getproductpriceandqty->product_quantity,
				'order_status' 			=> "Pending",
				'status_id' => 2,
				'created_by' => session()->get('id'),
				'created_at' => date('Y-m-d h:i:s')
				);
			DB::connection('mysql')->table('tuckorder')->insert($addproduct);
		if($getproductlist){
			return response()->json(['product' => $getproductlist],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}