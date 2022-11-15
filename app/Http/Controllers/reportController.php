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

class reportController extends Controller
{
	public function monthlytargetreport(Request $request){
		$getcompleteunpaidorder = 0;
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$targetdate = $year.'-'.$month;
		$getuserlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->whereIn('role_id',[3,4])
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getdesignerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->where('role_id','=',5)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getdigitizerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->where('role_id','=',6)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getbasiccompletetarget = DB::table('user')
		->select('user_target')
		->whereIn('role_id',[3,4,5,6])
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->sum('user_target');
		$completetargetincrement = DB::table('usertarget')
		->select('usertarget_target')
		->where('usertarget_month','<=',$targetdate)
		->where('status_id','=',1)
		->sum('usertarget_target');
		$getcompletetarget = $getbasiccompletetarget+$completetargetincrement;
		$getmonth = $request->usertarget_month;
		$getuserdetails = array();
		$getdesignerdetails = array();
		$getdigitizerdetails = array();
		$gettarget = DB::table('usertarget')
		->select('usertarget_target')
		->where('usertarget_month','=',$finalyearandmonth)
		->where('status_id','=',1)
		->sum('usertarget_target');	
		$getcompletelogotargetgross = DB::table('logoorder')
		->select('logoorder_amount')
		->where('status_id','=',1)
		->whereBetween('logoorder_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->sum('logoorder_amount');
		$getcompletelogotargetpaid = DB::table('logoorder')
		->select('logoorder_amount')
		->where('status_id','=',1)
		->where('orderstatus_id','=',11)
		->whereBetween('logoorder_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->sum('logoorder_amount');
		$getcompletewebtargetgross = DB::table('weborder')
		->select('weborder_amount')
		->where('status_id','=',1)
		->whereBetween('weborder_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->sum('weborder_amount');
		$getcompletewebtargetpaidmaster = DB::table('weborder')
		->select('weborder_remainingamount')
		->where('status_id','=',1)
		->where('orderstatus_id','=',11)
		->whereBetween('weborder_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->sum('weborder_remainingamount');	
		$getcompletewebtargetpaidmilestone = DB::table('webpaymentandorderdetail')
		->select('weborderpayment_amount')
		->where('status_id','=',1)
		->where('orderstatus_id','=',11)
		->whereBetween('weborderpayment_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->sum('weborderpayment_amount');	
		$getcompletewebtargetpaid = $getcompletewebtargetpaidmaster+$getcompletewebtargetpaidmilestone;
		$getcompletetargetachievedworker = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->whereIn('orderstatus_id',[5,6,7,8,9,10,11,16,17,18])
		->whereBetween('order_date', [$from, $to])
		// ->where('order_date','like',$getmonth.'%')
		->where('campaign_id','=',$request->campaign_id)
		->count('order_id');
		$getfullcompleteprderagent = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->whereIn('orderstatus_id',[7,8,9,10,11,17])
		->whereBetween('order_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->count('order_id');
		$getfullcompleteprderworker = $getcompletetargetachievedworker + $getfullcompleteprderagent;
		$getfullcompleteprder = $getfullcompleteprderagent;
		$getcompletetargetachieved = DB::table('order')
		->select('order_amountquoted')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->whereIn('orderstatus_id',[4,5,6,7,8,9,10,11,17,18])
		->whereBetween('order_date', [$from, $to])
		->sum('order_amountquoted');
		$getcompletetargetcancel = DB::table('order')
		->select('order_amountquoted')
		->where('status_id','=',1)
		->where('orderstatus_id','=',12)
		->whereBetween('order_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->sum('order_amountquoted');
		$getcompletetargetpaid = DB::table('order')
		->select('order_amountquoted')
		->where('status_id','=',1)
		->where('orderstatus_id','=',11)
		->whereBetween('order_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->sum('order_amountquoted');
		$getcompletetargetrecover = DB::table('order')
		->select('order_amountquoted')
		->where('status_id','=',1)
		->where('orderstatus_id','=',18)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->sum('order_amountquoted');
		$getcompletetotalorder = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->whereBetween('order_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->count('order_id');
		$getcompletepaidorder = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->where('orderstatus_id','=',11)
		->whereBetween('order_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->count('order_id');
		$getcompletecancelorder = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->where('orderstatus_id','=',12)
		->whereBetween('order_date', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->count('order_id');
		$getcompleterecoverorder = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->where('orderstatus_id','=',18)
		->whereBetween('order_recoverydate', [$from, $to])
		->where('campaign_id','=',$request->campaign_id)
		->count('order_id');
		$getcompletetargetunpaid = $getcompletetargetachieved-$getcompletetargetpaid-$getcompletetargetcancel; 
		$index=0;
		foreach ($getuserlist as $getuserlist) {
			$targetincrement = DB::table('usertarget')
			->select('usertarget_target')
			->where('user_id','=',$getuserlist->user_id)
			->where('usertarget_month','<=',$targetdate)
			->where('status_id','=',1)
			->sum('usertarget_target');
			$user_target = $getuserlist->user_target+$targetincrement;
			$getlogotargetgross = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->whereBetween('logoorder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('logoorder_amount');
			$getlogotargetpaid = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->where('orderstatus_id','=',11)
			->whereBetween('logoorder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('logoorder_amount');
			$getwebtargetgross = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->whereBetween('weborder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('weborder_amount');
			$getwebtargetpaidmaster = DB::table('weborder')
			->select('weborder_remainingamount')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->where('orderstatus_id','=',11)
			->whereBetween('weborder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('weborder_remainingamount');	
			$getwebtargetpaidmilestone = DB::table('webpaymentandorderdetail')
			->select('weborderpayment_amount')
			->where('status_id','=',1)
			->where('order_createdby','=',$getuserlist->user_id)
			->where('orderstatus_id','=',11)
			->whereBetween('weborderpayment_date', [$from, $to])
			->sum('weborderpayment_amount');	
			$getwebtargetpaid = $getwebtargetpaidmaster+$getwebtargetpaidmilestone;
			$mergeweblogotarget = $getwebtargetpaid+$getlogotargetpaid;
			$weblogocommission;
			$getweblogocommission = DB::table('commission')
			->select('*')
			->where('status_id','=',3)
			->get();
			$weblogoindex = 0;
			foreach ($getweblogocommission as $getweblogocommissions) {
				if ($mergeweblogotarget >= $getweblogocommissions->commission_from && $mergeweblogotarget <= $getweblogocommissions->commission_to && $weblogoindex == 0) {
					$weblogocommission = $getweblogocommissions->commission_rate;
					$weblogoindex++;
					break;
				}else{
					$weblogocommission = 0;
				}
			}
			$gettargetachieved = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->whereIn('orderstatus_id',[4,5,6,7,8,9,10,11,17,18])
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('order_amountquoted');
			$gettargetpaid = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->where('orderstatus_id','=',11)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('order_amountquoted');
			$gettargetrecover = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->where('orderstatus_id','=',18)
			->whereBetween('order_recoverydate', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('order_amountquoted');
			$gettargetcancel = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->where('orderstatus_id','=',12)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('order_amountquoted');
			$gettargetunpaid = $gettargetachieved-$gettargetpaid-$gettargetcancel; 
			$gettotalorder = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->where('orderstatus_id','!=',18)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getcompleteprder = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17])
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getpaidorder = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->where('orderstatus_id','=',11)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getcancelorder = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->where('orderstatus_id','=',12)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getrecoveryorder = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('created_by','=',$getuserlist->user_id)
			->where('orderstatus_id','=',18)
			->whereBetween('order_recoverydate', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getunpaidorder = $getcompleteprder-$getpaidorder-$getcancelorder;
			$getcompleteunpaidorder = $getfullcompleteprder-$getcompletepaidorder-$getcompletecancelorder;
			$getuserlist->user_target = $user_target;
			$getuserlist->webtargetgross = $getwebtargetgross;
			$getuserlist->webtargetpaid = $getwebtargetpaid;
			$getuserlist->logotargetgross = $getlogotargetgross;
			$getuserlist->logotargetpaid = $getlogotargetpaid;
			$getuserlist->weblogocommission = $weblogocommission;
			$getuserlist->targetacieved = $gettargetachieved;
			$getuserlist->targetpaid = $gettargetpaid;
			$getuserlist->targetrecover = $gettargetrecover;
			$getuserlist->targetunpaid = $gettargetunpaid;
			$getuserlist->targetcancel = $gettargetcancel;
			$getuserlist->totalorder = $gettotalorder;
			$getuserlist->completeprder = $getcompleteprder;
			$getuserlist->paidorder = $getpaidorder;
			$getuserlist->cancelorder = $getcancelorder;
			$getuserlist->unpaidorder = $getunpaidorder;
			$getuserlist->recoveryorder = $getrecoveryorder;
			$getuserdetails[$index] = $getuserlist;
			$index++;
		}
		// designer start
		$designerindex=0;
		$designercommission;
		foreach ($getdesignerlist as $getdesignerlist) {
			$gettargetachieveddesigner = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>',4)
			->where('order_workpickby','=',$getdesignerlist->user_id)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getlogotargetachieveddesigner = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>',4)
			->where('logoorder_workpickby','=',$getdesignerlist->user_id)
			->whereBetween('logoorder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('logoorder_id');
			$getwebtargetachieveddesigner = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>',4)
			->where('weborder_workpickby','=',$getdesignerlist->user_id)
			->whereBetween('weborder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('weborder_id');
			$mergerdesignerorder = $gettargetachieveddesigner+$getlogotargetachieveddesigner;
			$getcommission = DB::table('commission')
			->select('*')
			->where('status_id','=',1)
			// ->where('user_id','=',$getdesignerlist->user_id)
			->where('role_id','=',5)
			->orderBy('commission_id','DESC')
			->get();
			// dd($getcommission);
			$commissionindex = 0;
			foreach ($getcommission as $getcommissions) {
				if ($mergerdesignerorder >= $getcommissions->commission_from && $mergerdesignerorder >= $getcommissions->commission_to && $commissionindex == 0) {
					$designercommission = $getcommissions->commission_rate;
					$commissionindex++;
					break;
				}else{
					$designercommission = 0;
				}
			}
			$getcompleteorder = $gettargetachieveddesigner;
			$getdesignerlist->completeorder = $getcompleteorder;
			$getdesignerlist->completelogoorder = $getlogotargetachieveddesigner;
			$getdesignerlist->completeweborder = $getwebtargetachieveddesigner;
			$getdesignerlist->commission = $designercommission;
			$getdesignerdetails[$designerindex] = $getdesignerlist;
			$designerindex++;
		}
		// designer end
		// digitizer start
		$digitizerindex=0;
		$digitizercommission = 0;
		foreach ($getdigitizerlist as $getdigitizerlist) {
			$gettargetachieveddigitizer = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>',4)
			->where('order_workpickby','=',$getdigitizerlist->user_id)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getcommission = DB::table('commission')
			->select('*')
			->where('status_id','=',1)
			->where('user_id','=',$getdigitizerlist->user_id)
			->orderBy('commission_id','DESC')
			->get();
			// dd($getcommission);
			$commissionindex = 0;
			foreach ($getcommission as $getcommissions) {
				if ($gettargetachieveddigitizer >= $getcommissions->commission_from && $gettargetachieveddigitizer >= $getcommissions->commission_to && $commissionindex == 0) {
					$digitizercommission = $getcommissions->commission_rate;
					$commissionindex++;
					break;
				}else{
					$digitizercommission = 0;
				}
			}
			$getcompleteorder = $gettargetachieveddigitizer;
			$getdigitizerlist->completeorder = $getcompleteorder;
			$getdigitizerlist->commission = $digitizercommission;
			$getdigitizerdetails[$digitizerindex] = $getdigitizerlist;
			$digitizerindex++;
		}
		// digitizer end
			$summreport = array(
				'completetarget' => $getcompletetarget,
				'completetargetacieved' => $getcompletetargetachieved,
				'completetargetpaid' => $getcompletetargetpaid,
				'completetargetunpaid' => $getcompletetargetunpaid,
				'completetargetcancel' => $getcompletetargetcancel,
				'completetargetrecover' => $getcompletetargetrecover,
				'completetotalorder' => $getcompletetotalorder,
				'completecompleteprder' => $getfullcompleteprder,
				'completepaidorder' => $getcompletepaidorder,
				'completecancelorder' => $getcompletecancelorder,
				'completeunpaidorder' => $getcompleteunpaidorder,
				'completerecoverorder' => $getcompleterecoverorder,
				'completelogotargetgross' => $getcompletelogotargetgross,
				'completelogotargetpaid' => $getcompletelogotargetpaid,
				'completewebtargetgross' => $getcompletewebtargetgross,
				'completewebtargetpaid' => $getcompletewebtargetpaid,
			);
		if(isset($getuserlist)){
		return response()->json(['userdata' => $getuserdetails,'designerdata' => $getdesignerdetails ,'digitizerdata' => $getdigitizerdetails,'summofallreport' => $summreport,'message' => 'Monthly Employee Target Report'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function commissionreport(Request $request){
		$date = $request->date;
		$getyearandmonth = explode('-', $date);
		$list=array();
		$year = $getyearandmonth[0];
		$month = $getyearandmonth[1];
		for($d=1; $d<=31; $d++)
		{
		    $time=mktime(12, 0, 0, $month, $d, $year);          
		    if (date('m', $time)==$month)       
		    $list[]=date('Y-m-d', $time);
		}
		$commissiondata =  array();
		$achieveddate = '-';
		$finalcommisionamount=0;
		$finalrecoveryamount=0;
		$finalrate=0;
		$commissionindex=0;
		$indexforallpaidorders = 0;
		$finalpaidorders = 0;
		$finalrecoveryorders = 0;
		foreach ($list as $lists) {
			$getpaidamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('order_date', [$request->from, $lists])
			->sum('order.order_amountquoted');
			$getrecoveryamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',18)
			->whereBetween('order_recoverydate', [$request->from, $lists])
			->sum('order.order_amountquoted');
			$gettargetachieved = $getpaidamount + $getrecoveryamount;
			$getcommission = DB::table('commission')
			->select('commission_rate', 'commission_from' , 'commission_to')
			->where('status_id','=',1)
			->where('user_id','=',$request->id)
			->get();
			$commission = array();
			$index = 0;
			foreach ($getcommission as $getcommissions) {
				$commission[$index]['rate'] = $getcommissions->commission_rate;
				$commission[$index]['from'] = $getcommissions->commission_from;
				$commission[$index]['to'] = $getcommissions->commission_to;
				$index++;
			}
			foreach ($commission as $commissions) {
			if ($gettargetachieved >= $commissions['from'] && $gettargetachieved <= $commissions['to']) {
					$getpaid = DB::table('order')
					->select('order_id')
					->where('status_id','=',1)
					->where('campaign_id','=',$request->campaign_id)
					->where('created_by','=',$request->id)
					->where('orderstatus_id','=',11)
					->where('order_date','=',$lists)
					->count('order_id');
					$getrecovery = DB::table('order')
					->select('order_amountquoted')
					->where('status_id','=',1)
					->where('campaign_id','=',$request->campaign_id)
					->where('created_by','=',$request->id)
					->where('orderstatus_id','=',18)
					->where('order_recoverydate','=',$lists)
					->count('order_id');
					$getpaidorders = $getpaid;
					$getrecoveryorders = $getrecovery;
					if ($commissions['from'] != 1 && $gettargetachieved >= $commissions['from'] && $indexforallpaidorders == 0) {
					$achieveddate = $lists;
					$indexforallpaidorders++;
					}
				$finalcommisionamount = $commissions['rate']*$getpaidorders;
				$finalrecoveryamount = $commissions['rate']*$getrecoveryorders;
				$finalrate = $commissions['rate'];
				$finalpaidorders = $getpaidorders;
				$finalrecoveryorders = $getrecoveryorders;
				break;
			}else{
				$finalcommisionamount = 0;
				$finalrecoveryamount = 0;
				$finalrate = 0;
				$finalpaidorders = 0;
				$finalrecoveryorders = 0;
			}
				
			}
			$commissiondata[$commissionindex]['finalcommisionamount'] = $finalcommisionamount;
			$commissiondata[$commissionindex]['finalrecoveryamount'] = $finalrecoveryamount;
			$commissiondata[$commissionindex]['finalrate'] = $finalrate;
			$commissiondata[$commissionindex]['finalpaidorders'] = $finalpaidorders;
			$commissiondata[$commissionindex]['finalrecoveryorders'] = $finalrecoveryorders;
			$commissiondata[$commissionindex]['date'] = $lists;
			$commissionindex++;
		}
		return response()->json(['commissiondata' => $commissiondata, 'targetachieveddate' => $achieveddate, 'message' => 'Monthly Employee Commission Report'],200);
	}
}