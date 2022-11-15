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

class dashboardController extends Controller
{
	public function admindashboard(Request $request){
		$getuser = DB::table('user')
		->select('user_id','user.user_name','user_target')
		->where('status_id','=',1)
		->get();
		$getmonth = date('n');
		$alldata = array();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
		$index = 0;
		foreach ($getuser as $getusers) {
			$gettargetachieved = DB::table('order')
			->select('order_amountquoted')
			->where('created_by','=',$getusers->id)
			->where('orderstatus_id','=',11)
			->where('created_at','like',$getmonth.'%')
			->sum('order.order_amountquoted');	
			$getusers->achieved = $gettargetachieved;
			$getusers->remaining = $getusers->user_target - $gettargetachieved;
			$getusers->perday = $getusers->user_target / $workingdays;
			$alldata[$index] = $getusers;
			$index++;
		}
		if(isset($alldata)){
		return response()->json(['data' => $alldata,'message' => 'Admin Dashboard User Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function userdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$targetdate = $year.'-'.$month;
		$getmonth = $request->usertarget_month;
		// $alldata = array();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
		// dd($workingdays);
			$gettargetachieved = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->whereIn('orderstatus_id',[4,5,6,7,8,9,10,11,12,17,18])
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('order.order_amountquoted');	
			$gettargetpaid = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('order.order_amountquoted');	
			$getrecoverypaid = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',18)
			->whereBetween('order_recoverydate', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('order_amountquoted');
			$gettargetcancel = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->sum('order_amountquoted');
			$targetincrement = DB::table('usertarget')
			->select('usertarget_target')
			->where('user_id','=',$request->id)
			->where('usertarget_month','<=',$targetdate)
			->where('status_id','=',1)
			->sum('usertarget_target');
			$usertarget = $getuser->user_target+$targetincrement;
			$getunpaidamount = $gettargetachieved-$gettargetpaid-$gettargetcancel-$getrecoverypaid;
			$getuser->user_target = $usertarget;
			$getuser->achieved = $gettargetachieved;
			$getuser->paid = $gettargetpaid;
			$getuser->recovery = $getrecoverypaid;
			$getuser->unpaidamount = $getunpaidamount;
			$getuser->remaining = $getuser->user_target - $gettargetachieved;
			$getuser->perday = $getuser->user_target / $workingdays;
			$getuser->cancel = $gettargetcancel;
			// $alldata = $getuser;

			$list=array();
			$noofdays = date('t');

			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			// dd($list);
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','!=',18)
			->where('order_date','=',$lists)
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getdailyorderamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','!=',18)
			->where('order_date','=',$lists)
			->where('campaign_id','=',$request->campaign_id)
			->sum('order_amountquoted');
			// dd($lists);
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			// $alldata[] = $getuser;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','!=',18)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');

			$getmonthlycompleteorders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,11,12,17])
			->where('created_by','=',$request->id)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');

			$getmonthlypaidorders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('created_by','=',$request->id)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');

			$getmonthlyrecoveryorders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',18)
			->where('created_by','=',$request->id)
			->whereBetween('order_recoverydate', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');

			$gettargetcancel = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getmonthlypendingorders = $getmonthlycompleteorders-$getmonthlypaidorders-$gettargetcancel;

			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['paidorder'] = $getmonthlypaidorders;
			$ordercounts['cancelorder'] = $gettargetcancel;
			$ordercounts['recoveryorder'] = $getmonthlyrecoveryorders;
			$ordercounts['pendingorder'] = $getmonthlypendingorders;

			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
			// dd($daysRemaining);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'User Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function agentlistfordashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		if($request->role_id == 3){
		$getagentlist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',4)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getagentlistdetails = array();
		$agentindex=0;
		foreach ($getagentlist as $getagentlist) {
			$gettargetachieved = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$getagentlist->user_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17,18])
			->whereBetween('order_date', [$from, $to])
			->sum('order_amountquoted');
			$getagentlist->targetacieved = $gettargetachieved;
			$getagentlistdetails[$agentindex] = $getagentlist;
			$agentindex++;
		}
		$getleadlist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',12)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
			return response()->json(['agent' => $getagentlistdetails, 'leadlist' => $getleadlist, 'message' => 'User List'],200);
		}else{
		$getagentlist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->whereNotIn('role_id', [1,9])
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getmanagerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',3)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getbdmanagerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',7)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getbillinglist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',2)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getagentlist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',4)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getdesignerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',5)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getdegitizerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',6)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getchaselist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',10)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getleadlist = DB::table('user')
		->select('user_id','role_id','user_name','user_email','user_phonenumber','user_target','user_loginstatus','user_picture','user_type','campaign_id','user_coverpicture')
		->where('role_id','=',12)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getmonth = $request->usertarget_month;
		$getmanagerlistdetails = array();
		$mangerindex=0;
		foreach ($getmanagerlist as $getmanagerlist) {
			$gettargetachieved = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$getmanagerlist->user_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17,18])
			->whereBetween('order_date', [$from, $to])
			->sum('order_amountquoted');
			$getmanagerlist->targetacieved = $gettargetachieved;
			$getmanagerlistdetails[$mangerindex] = $getmanagerlist;
			$mangerindex++;
		}

		$getbdmanagerlistdetails = array();
		$mangerbdindex=0;
		foreach ($getbdmanagerlist as $getbdmanagerlist) {
			$gettargetachieved = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$getbdmanagerlist->user_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17,18])
			->whereBetween('order_date', [$from, $to])
			->sum('order_amountquoted');
			$getbdmanagerlist->targetacieved = $gettargetachieved;
			$getbdmanagerlistdetails[$mangerbdindex] = $getbdmanagerlist;
			$mangerbdindex++;
		}

		$getagentlistdetails = array();
		$agentindex=0;
		foreach ($getagentlist as $getagentlist) {
			$gettargetachieved = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('created_by','=',$getagentlist->user_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17,18])
			->whereBetween('order_date', [$from, $to])
			->sum('order_amountquoted');
			$getagentlist->targetacieved = $gettargetachieved;
			$getagentlistdetails[$agentindex] = $getagentlist;
			$agentindex++;
		}

		$getbillinglistdetails = array();
		$billingindex=0;
		foreach ($getbillinglist as $getbillinglist) {
			$gettargetachieved = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('order_billingby','=',$getbillinglist->user_id)
			->where('orderstatus_id','=',11)
			->whereBetween('order_date', [$from, $to])
			->sum('order.order_amountquoted');
			$getbillinglist->targetacieved = $gettargetachieved;
			$getbillinglistdetails[$billingindex] = $getbillinglist;
			$billingindex++;
		}

		$getdesignerlistdetails = array();
		$designerindex=0;
		foreach ($getdesignerlist as $getdesignerlist) {
			$gettargetachieved = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[5,6,7,8,9,10,11,16,17,18])
			->where('order_workpickby','=',$getdesignerlist->user_id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');
			$getdesignerlist->targetacieved = $gettargetachieved;
			$getdesignerlistdetails[$designerindex] = $getdesignerlist;
			$designerindex++;
		}

		$getdegitizerlistdetails = array();
		$degitizerindex=0;
		foreach ($getdegitizerlist as $getdegitizerlist) {
			$gettargetachieved = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[5,6,7,8,9,10,11,16,17,18])
			->where('order_workpickby','=',$getdegitizerlist->user_id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');
			$getdegitizerlist->targetacieved = $gettargetachieved;
			$getdegitizerlistdetails[$degitizerindex] = $getdegitizerlist;
			$degitizerindex++;
		}
		return response()->json(['manager' => $getmanagerlistdetails, 'bdmanager' => $getbdmanagerlistdetails, 'billing' => $getbillinglistdetails, 'agent' => $getagentlistdetails, 'designer' => $getdesignerlistdetails,'digitizer' => $getdegitizerlistdetails, 'doctorchase' => $getchaselist, 'leadlist' => $getleadlist,  'message' => 'User List'],200);
		}
	}
	public function admincampaigndashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$targetdate = $year.'-'.$month;
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->first();
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$basictarget = DB::table('user')
		->select('user_target')
		->where('campaign_id','=',$request->campaign_id)
		->whereIn('role_id',[3,4])
		->where('status_id','=',1)
		->sum('user_target');
		$targetincrement = DB::table('usertarget')
		->select('usertarget_target')
		->where('usertarget_month','<=',$targetdate)
		->where('status_id','=',1)
		->sum('usertarget_target');
		$getcampaigntarget = $basictarget+$targetincrement;
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$getgrosssale = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[4,5,6,7,8,9,10,11,17,18])
			->whereBetween('order_date', [$from, $to])
			->sum('order_amountquoted');
			$getpaidamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',11)
			->whereBetween('order_date', [$from, $to])
			->sum('order.order_amountquoted');
			$getcancelamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->whereBetween('order_date', [$from, $to])
			->sum('order_amountquoted');
			$getunpaidamount = $getgrosssale-$getpaidamount-$getcancelamount;
			$getpreviousunpaidamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,17])
			->where('order_date','<', $from)
			->sum('order_amountquoted');
			$getpreviouscancelamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->where('order_date','<', $from)
			->sum('order_amountquoted');
			$getpreviousunpaid = $getpreviousunpaidamount;
			$gettotalunpaidamount = $getunpaidamount+$getpreviousunpaid;
			$getpreviousrecovery = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',18)
			->whereBetween('order_recoverydate', [$from, $to])
			->sum('order.order_amountquoted');
			$getremainingunpaidamount = 0;
			$getperdaygrosssale = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17,18])
			->where('order_date','=',$to)
			->sum('order.order_amountquoted');
			$getperdaypaidamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',11)
			->where('order_paiddate','=',$to)
			->sum('order.order_amountquoted');
			$getperdayunpaidamount = $getperdaygrosssale - $getperdaypaidamount;

			$getuser->campaigntarget = $getcampaigntarget;
			$getuser->grosssale = $getgrosssale;
			$getuser->paidamount = $getpaidamount;
			$getuser->cancelamount = $getcancelamount;
			$getuser->unpaidamount = $getunpaidamount;
			$getuser->previousunpaid = $getpreviousunpaid;
			$getuser->previouscancel = $getpreviouscancelamount;
			$getuser->totalunpaidamount = $gettotalunpaidamount;
			$getuser->previousrecovery = $getpreviousrecovery;
			$getuser->remainingunpaidamount = $getremainingunpaidamount;
			$getuser->perdaygrosssale = $getperdaygrosssale;
			$getuser->perdaypaidamount = $getperdaypaidamount;
			$getuser->perdayunpaidamount = $getperdayunpaidamount;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('order_date','=',$lists)
			->count('order_id');
			$getdailyorderamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('order_date','=',$lists)
			->sum('order_amountquoted');
			// dd($lists);
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			// $alldata[] = $getuser;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');
			$getmonthlycompleteorders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[4,5,6,7,8,9,10,11,17,18])
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');
			$getmonthlypendingorders =  DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');
			$getmonthlypaidorders = $getmonthlycompleteorders-$getmonthlypendingorders;

			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['paidorder'] = $getmonthlypaidorders;
			$ordercounts['pendingorder'] = $getmonthlypendingorders;

			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
			// dd($daysRemaining);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'User Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function billingdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getmonth = $request->usertarget_month;
		$getbillingtarget = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('order_billingby','=',$request->id)
			->whereIn('orderstatus_id',[9,10,11])
			->whereBetween('order_date', [$from, $to])
			->sum('order.order_amountquoted');
		// $alldata = array();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
		// dd($workingdays);
		$gettargetachieved = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('order_billingby','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('order_date', [$from, $to])
			->sum('order.order_amountquoted');	
			$gettargetpaid = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('order_billingby','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('order_date', [$from, $to])
			->sum('order.order_amountquoted');	
			$gettargetunpaid = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('order_billingby','=',$request->id)
			->whereIn('orderstatus_id',[9,10])
			->whereBetween('order_date', [$from, $to])
			->sum('order.order_amountquoted');	
			$getunpaidamount = $gettargetunpaid;
			$getuser->user_target = $getbillingtarget;
			$getuser->achieved = $gettargetachieved;
			$getuser->paid = $gettargetpaid;
			$getuser->unpaidamount = $getunpaidamount;
			$getuser->remaining = $getbillingtarget - $gettargetachieved;
			$getuser->perday = $getbillingtarget / $workingdays;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('order_billingby','=',$request->id)
			->where('order_date','=',$lists)
			->count('order_id');
			$getdailyorderamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('order_billingby','=',$request->id)
			->where('order_date','=',$lists)
			->sum('order_amountquoted');
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('order_billingby','=',$request->id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');

			$getmonthlycompleteorders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('order_billingby','=',$request->id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');

			$getmonthlypaidorders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',10)
			->where('order_billingby','=',$request->id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');
			$getmonthlypendingorders = $getmonthlytotalnooforders-$getmonthlycompleteorders;

			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['paidorder'] = $getmonthlypaidorders;
			$ordercounts['pendingorder'] = $getmonthlypendingorders;

			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
			// dd($daysRemaining);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'User Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function workerdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getmonth = $request->usertarget_month;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('order_workpickby','=',$request->id)
			->where('order_date','=',$lists)
			->count('order_id');
			$getdailyorderamount = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->where('order_workpickby','=',$request->id)
			->where('order_date','=',$lists)
			->sum('order_amountquoted');
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('order_workpickby','=',$request->id)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getmonthlycompleteorders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>',4)
			->where('order_workpickby','=',$request->id)
			->whereBetween('order_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('order_id');
			$getmonthlyremainingorders = $getmonthlytotalnooforders-$getmonthlycompleteorders;
			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['pendingorder'] = $getmonthlyremainingorders;
			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'Worker Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmedashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getcampaigntarget = DB::table('user')
		->select('user_target')
		->where('campaign_id','=',$request->campaign_id)
		->whereIn('role_id',[3,4])
		->where('status_id','=',1)
		->sum('user_target');
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
				// dd($lists);
			$getdailynoofordersforwarded = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',2)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynooforderssubmited = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',20)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersforwardedtobilling = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',8)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersapproved = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',19)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$datewiseordercount[$index]['dailynoofordersforwarded'] = $getdailynoofordersforwarded;
			$datewiseordercount[$index]['dailyno1oforderssubmited'] = $getdailynooforderssubmited;
			$datewiseordercount[$index]['dailynoofordersforwardedtobilling'] = $getdailynoofordersforwardedtobilling;
			$datewiseordercount[$index]['dailynoofordersapproved'] = $getdailynoofordersapproved;
			$datewiseordercount[$index]['orderdate'] = $lists;
			// $alldata[] = $getuser;
			$index++;
			}
			$getmonthlyemployeefowardedorders = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',2)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlyemployeesubmitedorders = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',20)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlyemployeeforwardedtobillingorders = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',8)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlyemployeeapprovedorders = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',19)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$monthlyemployeeordercounts = array();
			$monthlyemployeeordercounts['monthlyemployeefowardedorders'] = $getmonthlyemployeefowardedorders;
			$monthlyemployeeordercounts['monthlyemployeesubmitedorders'] = $getmonthlyemployeesubmitedorders;
			$monthlyemployeeordercounts['monthlyemployeeforwardedtobillingorders'] = $getmonthlyemployeeforwardedtobillingorders;
			$monthlyemployeeordercounts['monthlyemployeeapprovedorders'] = $getmonthlyemployeeapprovedorders;
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'dailyordercount' => $datewiseordercount, 'monthlyemployeeordercounts' => $monthlyemployeeordercounts, 'message' => 'DME Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmeagentdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$gettotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getsaveorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getforwardedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',2)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getppoorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"PPO")
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getpartborder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getotherorder = $getforwardedorder-$getppoorder-$getpartborder;
			//old
			$datewiseordercount[$index]['totalorder'] = $gettotalorder;
			$datewiseordercount[$index]['saveorder'] = $getsaveorder;
			$datewiseordercount[$index]['forwardedorder'] = $getforwardedorder;
			$datewiseordercount[$index]['ppoorder'] = $getppoorder;
			$datewiseordercount[$index]['partborder'] = $getpartborder;
			$datewiseordercount[$index]['otherorder'] = $getotherorder;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$gettodaytotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodaysaveorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodayforwardedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',2)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodayppoorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"PPO")
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodaypartborder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodayotherorder = $gettodayforwardedorder-$gettodayppoorder-$getpartborder;
			$todayordercounts = array();
			$todayordercounts['totalorder'] = $gettodaytotalorder;
			$todayordercounts['saveorder'] = $gettodaysaveorder;
			$todayordercounts['forwardedorder'] = $gettodayforwardedorder;
			$todayordercounts['ppoorder'] = $gettodayppoorder;
			$todayordercounts['partborder'] = $gettodaypartborder;
			$todayordercounts['otherorder'] = $gettodayotherorder;
			$getmonthlytotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlysaveorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyforwardedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',2)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyppoorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"PPO")
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlypartborder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyotherorder = $getmonthlyforwardedorder-$getmonthlyppoorder-$getpartborder;
			$monthlyordercounts = array();
			$monthlyordercounts['totalorder'] = $getmonthlytotalorder;
			$monthlyordercounts['saveorder'] = $getmonthlysaveorder;
			$monthlyordercounts['forwardedorder'] = $getmonthlyforwardedorder;
			$monthlyordercounts['ppoorder'] = $getmonthlyppoorder;
			$monthlyordercounts['partborder'] = $getmonthlypartborder;
			$monthlyordercounts['otherorder'] = $getmonthlyotherorder;
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'dailyordercount' => $datewiseordercount, 'todayordercounts' => $todayordercounts, 'monthlyordercounts' => $monthlyordercounts, 'message' => 'DME Agent Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmemanagerdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getcampaigntarget = DB::table('user')
		->select('user_target')
		->where('campaign_id','=',$request->campaign_id)
		->whereIn('role_id',[3,4])
		->where('status_id','=',1)
		->sum('user_target');
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailytotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',2)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getdailypickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',3)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getdailysubmitorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',20)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getdailyforwardedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getdailyppoorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"PPO")
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getdailypartborder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->where('dmeclient_managerpickby','=',$request->id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getdailyreturntoagentorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',6)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getdailyreturntomanagerorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->where('dmeclient_isreturnfromchase','=',1)
			->orwhere('dmeclient_isreturnfrombilling','=',1)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$datewiseordercount[$index]['dailytotalorder'] = $getdailytotalorder;
			$datewiseordercount[$index]['dailypickorder'] = $getdailypickorder;
			$datewiseordercount[$index]['dailysubmitorder'] = $getdailysubmitorder;
			$datewiseordercount[$index]['dailyforwardedorder'] = $getdailyforwardedorder;
			$datewiseordercount[$index]['dailyppoorder'] = $getdailyppoorder;
			$datewiseordercount[$index]['dailypartborder'] = $getdailypartborder;
			$datewiseordercount[$index]['dailyreturntoagentorder'] = $getdailyreturntoagentorder;
			$datewiseordercount[$index]['dailyreturntomanagerorder'] = $getdailyreturntomanagerorder;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlytotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',2)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlypickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',3)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlysubmitorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',20)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyforwardedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyppoorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"PPO")
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlypartborder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->where('dmeclient_managerpickby','=',$request->id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyreturntoagentorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',6)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyreturntomanagerorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->where('dmeclient_isreturnfromchase','=',1)
			->orwhere('dmeclient_isreturnfrombilling','=',1)
			->where('dmeclient_managerpickby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getuser->campaigntarget = $getcampaigntarget;
			$monthlyordercounts = array();
			$monthlyordercounts['monthlytotalorder'] = $getmonthlytotalorder;
			$monthlyordercounts['monthlypickorder'] = $getmonthlypickorder;
			$monthlyordercounts['monthlysubmitorder'] = $getmonthlysubmitorder;
			$monthlyordercounts['monthlyforwardedorder'] = $getmonthlyforwardedorder;
			$monthlyordercounts['monthlyppoorder'] = $getmonthlyppoorder;
			$monthlyordercounts['monthlypartborder'] = $getmonthlypartborder;
			$monthlyordercounts['monthlyreturntoagentorder'] = $getmonthlyreturntoagentorder;
			$monthlyordercounts['monthlyreturntomanagerorder'] = $getmonthlyreturntomanagerorder;
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'dailyordercount' => $datewiseordercount, 'monthlyordercounts' => $monthlyordercounts, 'message' => 'DME Manager Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmebillingdashboard(Request $request){
		$getmanagerlist = DB::table('user')
		->select('user_id')
		->where('role_id','=',3)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$sortmanager = array();
		foreach ($getmanagerlist as $getmanagerlist) {
			$sortmanager[] = $getmanagerlist->user_id;
		}
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$gettotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereIn('dmeclient_managerpickby',$sortmanager)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getpickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getprocessorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('dmeclient_isprocess','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getnonchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('dmeclient_isnonprocess','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getreturntomanager = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_isreturnfrombilling','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getcreatedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$datewiseordercount[$index]['gettotalorder'] = $gettotalorder;
			$datewiseordercount[$index]['getpickorder'] = $getpickorder;
			$datewiseordercount[$index]['getprocessorder'] = $getprocessorder;
			$datewiseordercount[$index]['getnonchaseorder'] = $getnonchaseorder;
			$datewiseordercount[$index]['getreturntomanager'] = $getreturntomanager;
			$datewiseordercount[$index]['getcreatedorder'] = $getcreatedorder;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$gettodaytotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereIn('dmeclient_managerpickby',$sortmanager)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodaypickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodayprocessorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('dmeclient_isprocess','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodaynonchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('dmeclient_isnonprocess','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodayreturntomanagerorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_isreturnfrombilling','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodaycreatedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$todayordercounts = array();
			$todayordercounts['todaytotalorder'] = $gettodaytotalorder;
			$todayordercounts['todaypickorder'] = $gettodaypickorder;
			$todayordercounts['todayprocessorder'] = $gettodayprocessorder;
			$todayordercounts['todaynonchaseorder'] = $gettodaynonchaseorder;
			$todayordercounts['todayreturntomanagerorder'] = $gettodayreturntomanagerorder;
			$todayordercounts['todaycreatedorder'] = $gettodaycreatedorder;
			$getmonthlytotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereIn('dmeclient_managerpickby',$sortmanager)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlypickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyprocessorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('dmeclient_isprocess','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlynonchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('dmeclient_isnonprocess','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyreturntomanagerorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_isreturnfrombilling','=',1)
			->where('dmeclient_billingby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlycreatedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$monthlyordercounts = array();
			$monthlyordercounts['monthlytotalorder'] = $getmonthlytotalorder;
			$monthlyordercounts['monthlypickorder'] = $getmonthlypickorder;
			$monthlyordercounts['monthlyprocessorder'] = $getmonthlyprocessorder;
			$monthlyordercounts['monthlynonchaseorder'] = $getmonthlynonchaseorder;
			$monthlyordercounts['monthlyreturntomanagerorder'] = $getmonthlyreturntomanagerorder;
			$monthlyordercounts['monthlycreatedorder'] = $getmonthlycreatedorder;
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'dailyordercount' => $datewiseordercount, 'todayordercounts' => $todayordercounts, 'monthlyordercounts' => $monthlyordercounts, 'message' => 'DME Billing Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmeadmindashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforderstarget = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[8,12,19,21,22,23,24])
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersfilled = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isfilled','=',1)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersprocessed = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isprocess','=',1)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordernooprocessed = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isnonprocess','=',1)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersarchived = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_isarchived','=',$request->campaign_id)
			->where('dmeclient_ispv','=',1)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynooforderapproved = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',19)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordercancel = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynooforderreturntomanager = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',25)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynooforderfaxsend = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_ispv','=',1)
			->where('orderstatus_id','=',26)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersubmited = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',20)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynooforderforwardedtomanager = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',2)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynooforderforwardedtobilling = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',8)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynooforderchased = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeotherdetails_chase','=',"yes")
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordernonchased = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeotherdetails_chase','=',"no")
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$getdailynooforderlivetransfer = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_islivetransfer','=',1)
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$datewiseordercount[$index]['dailynooforderstarget'] = $getdailynooforderstarget;
			$datewiseordercount[$index]['dailynoofordersfilled'] = $getdailynoofordersfilled;
			$datewiseordercount[$index]['dailynoofordersprocessed'] = $getdailynoofordersprocessed;
			$datewiseordercount[$index]['dailynoofordernooprocessed'] = $getdailynoofordernooprocessed;
			$datewiseordercount[$index]['dailynoofordersarchived'] = $getdailynoofordersarchived;
			$datewiseordercount[$index]['dailynooforderapproved'] = $getdailynooforderapproved;
			$datewiseordercount[$index]['dailynoofordercancel'] = $getdailynoofordercancel;
			$datewiseordercount[$index]['dailynooforderreturntomanager'] = $getdailynooforderreturntomanager;
			$datewiseordercount[$index]['dailynooforderfaxsend'] = $getdailynooforderfaxsend;
			$datewiseordercount[$index]['dailynoofordersubmited'] = $getdailynoofordersubmited;
			$datewiseordercount[$index]['dailynooforderforwardedtomanager'] = $getdailynooforderforwardedtomanager;
			$datewiseordercount[$index]['dailynooforderforwardedtobilling'] = $getdailynooforderforwardedtobilling;
			$datewiseordercount[$index]['dailynooforderchased'] = $getdailynooforderchased;
			$datewiseordercount[$index]['dailynoofordernonchased'] = $getdailynoofordernonchased;
			$datewiseordercount[$index]['dailynooforderlivetransfer'] = $getdailynooforderlivetransfer;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$gettodaynooforderstarget = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[8,12,19,21,22,23,24])
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordersfilled = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isfilled','=',1)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordersprocessed = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isprocess','=',1)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordernooprocessed = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isnonprocess','=',1)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordersarchived = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isarchived','=',1)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynooforderapproved = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',19)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordercancel = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynooforderreturntomanager = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',25)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynooforderfaxsend = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_ispv','=',1)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordersubmited = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',20)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynooforderforwardedtomanager = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','>=',2)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynooforderforwardedtobilling = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',8)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynooforderchased = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeotherdetails_chase','=',"yes")
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordernonchased = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeotherdetails_chase','=',"no")
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodaynooforderlivetransfer = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_islivetransfer','=',1)
			->where('dmeclient_date','=',$to)
			->count('dmeclient_id');
			$gettodayremaining = $gettodaynooforderstarget - $gettodaynoofordersprocessed - $gettodaynoofordernooprocessed;
			$gettodaynoofordersarchived = $gettodaynooforderapproved - $gettodaynoofordercancel;
			$todayordercounts = array();
			$todayordercounts['todaynooforderstarget'] = $gettodaynooforderstarget;
			$todayordercounts['todaynoofordersfilled'] = $gettodaynoofordersfilled;
			$todayordercounts['todaynoofordersprocessed'] = $gettodaynoofordersprocessed;
			$todayordercounts['todaynoofordernooprocessed'] = $gettodaynoofordernooprocessed;
			$todayordercounts['todaynoofordersarchived'] = $gettodaynoofordersarchived;
			$todayordercounts['todaynooforderapproed'] = $gettodaynooforderapproved;
			$todayordercounts['todaynoofordercancel'] = $gettodaynoofordercancel;
			$todayordercounts['todaynooforderreturntomanager'] = $gettodaynooforderreturntomanager;
			$todayordercounts['todaynooforderfaxsend'] = $gettodaynooforderfaxsend;
			$todayordercounts['todaynoofordersubmited'] = $gettodaynoofordersubmited;
			$todayordercounts['todaynooforderforwardedtomanager'] = $gettodaynooforderforwardedtomanager;
			$todayordercounts['todaynooforderforwardedtobilling'] = $gettodaynooforderforwardedtobilling;
			$todayordercounts['todaynooforderchased'] = $gettodaynooforderchased;
			$todayordercounts['todaynoofordernonchased'] = $gettodaynoofordernonchased;
			$todayordercounts['todaynooforderlivetransfer'] = $gettodaynooforderlivetransfer;
			$todayordercounts['todayremaining'] = $gettodayremaining;
			$getmonthlynooforderstarget = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[8,12,19,21,22,23,24])
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersfilled = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isfilled','=',1)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersprocessed = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isprocess','=',1)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordernooprocessed = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isnonprocess','=',1)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersarchived = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isarchived','=',1)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderapproved = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',19)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordercancel = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderreturntomanager = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',25)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderfaxsend = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_ispv','=',1)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersubmited = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',20)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderforwardedtomanager = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','>=',2)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderforwardedtobilling = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',8)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderchased = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeotherdetails_chase','=',"yes")
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordernonchased = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeotherdetails_chase','=',"no")
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderleadreject = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',30)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderleadpending = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',32)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderleadapprove = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',28)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderleadpaid = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',31)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderpartb = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderppo = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cardtype','=',"PPO")
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynooforderlivetransfer = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_islivetransfer','=',1)
			->whereBetween('dmeclient_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('dmeclient_id');
			$getmomthlyremaining = $getmonthlynooforderstarget - $getmonthlynoofordersprocessed - $getmonthlynoofordernooprocessed; 
			$getmonthlynoofordersarchived = $getmonthlynooforderapproved - $getmonthlynoofordercancel;
			$monthlyordercounts = array();
			$monthlyordercounts['monthlynooforderstarget'] = $getmonthlynooforderstarget;
			$monthlyordercounts['monthlynoofordersfilled'] = $getmonthlynoofordersfilled;
			$monthlyordercounts['monthlynoofordersprocessed'] = $getmonthlynoofordersprocessed;
			$monthlyordercounts['monthlynoofordernooprocessed'] = $getmonthlynoofordernooprocessed;
			$monthlyordercounts['monthlynoofordersarchived'] = $getmonthlynoofordersarchived;
			$monthlyordercounts['monthlynooforderapproed'] = $getmonthlynooforderapproved;
			$monthlyordercounts['monthlynoofordercancel'] = $getmonthlynoofordercancel;
			$monthlyordercounts['monthlynooforderreturntomanager'] = $getmonthlynooforderreturntomanager;
			$monthlyordercounts['monthlynooforderfaxsend'] = $getmonthlynooforderfaxsend;
			$monthlyordercounts['monthlynoofordersubmited'] = $getmonthlynoofordersubmited;
			$monthlyordercounts['monthlynooforderforwardedtomanager'] = $getmonthlynooforderforwardedtomanager;
			$monthlyordercounts['monthlynooforderforwardedtobilling'] = $getmonthlynooforderforwardedtobilling;
			$monthlyordercounts['monthlynooforderchased'] = $getmonthlynooforderchased;
			$monthlyordercounts['monthlynoofordernonchased'] = $getmonthlynoofordernonchased;
			$monthlyordercounts['getmonthlynooforderleadreject'] = $getmonthlynooforderleadreject;
			$monthlyordercounts['getmonthlynooforderleadpending'] = $getmonthlynooforderleadpending;
			$monthlyordercounts['getmonthlynooforderleadapprove'] = $getmonthlynooforderleadapprove;
			$monthlyordercounts['getmonthlynooforderleadpaid'] = $getmonthlynooforderleadpaid;
			$monthlyordercounts['getmonthlynooforderpartb'] = $getmonthlynooforderpartb;
			$monthlyordercounts['getmonthlynooforderppo'] = $getmonthlynooforderppo;
			$monthlyordercounts['monthlynooforderlivetransfer'] = $getmonthlynooforderlivetransfer;
			$monthlyordercounts['momthlyremaining'] = $getmomthlyremaining;

			$merchatwisecount = array();
			$getmerchat = DB::table('dmemerchant')
			->select('dmemerchant_id','dmemerchant_name')
			->where('status_id','=',1)
			->get();
			$merchantindex=0;
			foreach ($getmerchat as $getmerchats) {
			$merchatwisecount[$merchantindex]['noofdeal'] = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isprocess','=',1)
			->where('dmemerchant_id','=',$getmerchats->dmemerchant_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$merchatwisecount[$merchantindex]['dmemerchant_id'] = $getmerchats->dmemerchant_id;
			$merchatwisecount[$merchantindex]['dmemerchant_name'] = $getmerchats->dmemerchant_name;
			$merchantindex++;
			}
			$servicewisecount = array();
			$getservice = DB::table('dmeservices')
			->select('dmeservices_id','dmeservices_name')
			->where('status_id','=',1)
			->get();
			$serviceindex=0;
			foreach ($getservice as $getservices) {
			$servicewisecount[$serviceindex]['noofdeal'] = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_isprocess','=',1)
			->where('dmeservices_id','=',$getservices->dmeservices_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count('dmeclient_id');
			$servicewisecount[$serviceindex]['dmeservices_id'] = $getservices->dmeservices_id;
			$servicewisecount[$serviceindex]['dmeservices_name'] = $getservices->dmeservices_name;
			$serviceindex++;
			}
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'dailyordercount' => $datewiseordercount, 'monthlyordercounts' => $monthlyordercounts , 'todayordercounts' => $todayordercounts, 'merchatcount' => $merchatwisecount, 'servicewisecount' => $servicewisecount, 'message' => 'DME Admin Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmedoctorchasedashboard(Request $request){
		$getmanagerlist = DB::table('user')
		->select('user_id')
		->where('role_id','=',3)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$sortmanager = array();
		foreach ($getmanagerlist as $getmanagerlist) {
			$sortmanager[] = $getmanagerlist->user_id;
		}
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$getdmenonchasedclientid = db::table('dmeotherdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeotherdetails_chase','=',"No")
			->get();
			$dmenonchasedclientids = array();
			foreach ($getdmenonchasedclientid as $getdmenonchasedclientids) {
				$dmenonchasedclientids[] = $getdmenonchasedclientids->dmeclient_id;
			}
			$getdmechasedclientid = db::table('dmeotherdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeotherdetails_chase','=',"No")
			->get();
			$dmechasedclientids = array();
			foreach ($getdmechasedclientid as $getdmechasedclientids) {
				$dmechasedclientids[] = $getdmechasedclientids->dmeclient_id;
			}
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$gettotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereIn('dmeclient_managerpickby',$sortmanager)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getpickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('dmeotherdetails_chase','=',"yes")
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getnonchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('dmeotherdetails_chase','=',"no")
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getreturntomanagerorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_isreturnfromchase','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$getcreatedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$lists)
			->count();
			$datewiseordercount[$index]['gettotalorder'] = $gettotalorder;
			$datewiseordercount[$index]['getpickorder'] = $getpickorder;
			$datewiseordercount[$index]['getchaseorder'] = $getchaseorder;
			$datewiseordercount[$index]['getnonchaseorder'] = $getnonchaseorder;
			$datewiseordercount[$index]['getreturntomanagerorder'] = $getreturntomanagerorder;
			$datewiseordercount[$index]['getcreatedorder'] = $getcreatedorder;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$gettodaytotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereIn('dmeclient_managerpickby',$sortmanager)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodaypickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodaychaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('dmeotherdetails_chase','=',"yes")
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodaynonchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('dmeotherdetails_chase','=',"no")
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodayreturntomanagerorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_isreturnfromchase','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$gettodaycreatedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_date','=',$to)
			->count();
			$todayordercounts = array();
			$todayordercounts['todaytotalorder'] = $gettodaytotalorder;
			$todayordercounts['todaypickorder'] = $gettodaypickorder;
			$todayordercounts['todaychaseorder'] = $gettodaychaseorder;
			$todayordercounts['todaynonchaseorder'] = $gettodaynonchaseorder;
			$todayordercounts['todayreturntomanagerorder'] = $gettodayreturntomanagerorder;
			$todayordercounts['todaycreatedorder'] = $gettodaycreatedorder;
			$getmonthlytotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereIn('dmeclient_managerpickby',$sortmanager)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlypickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlychaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('dmeotherdetails_chase','=',"yes")
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlynonchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('dmeotherdetails_chase','=',"no")
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlyreturntomanagerorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_isreturnfromchase','=',1)
			->where('dmeclient_chaseby','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getmonthlycreatedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$monthlyordercounts = array();
			$monthlyordercounts['monthlytotalorder'] = $getmonthlytotalorder;
			$monthlyordercounts['monthlypickorder'] = $getmonthlypickorder;
			$monthlyordercounts['monthlychaseorder'] = $getmonthlychaseorder;
			$monthlyordercounts['monthlynonchaseorder'] = $getmonthlynonchaseorder;
			$monthlyordercounts['monthlyreturntomanagerorder'] = $getmonthlyreturntomanagerorder;
			$monthlyordercounts['monthlycreatedorder'] = $getmonthlycreatedorder;
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'dailyordercount' => $datewiseordercount, 'todayordercounts' => $todayordercounts, 'monthlyordercounts' => $monthlyordercounts, 'message' => 'DME Doctor Chase Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function adminlogodashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->first();
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$getcampaigntarget = DB::table('user')
		->select('user_target')
		->where('campaign_id','=',$request->campaign_id)
		->whereIn('role_id',[3,4])
		->where('status_id','=',1)
		->sum('user_target');
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$getgrosssale = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17,18])
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');
			$getpaidamount = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[11,18])
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');
			$getcancelamount = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');
			$getunpaidamount = $getgrosssale-$getpaidamount-$getcancelamount;
			$getpreviousunpaidamount = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,12,17])
			->where('logoorder_date','<', $from)
			->sum('logoorder_amount');
			$getpreviouscancelamount = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->where('logoorder_date','<', $from)
			->sum('logoorder_amount');
			$getpreviousunpaid = $getpreviousunpaidamount-$getpreviouscancelamount;
			$gettotalunpaidamount = $getunpaidamount+$getpreviousunpaid;
			$getremainingunpaidamount = 0;
			$getuser->campaigntarget = $getcampaigntarget;
			$getuser->grosssale = $getgrosssale;
			$getuser->paidamount = $getpaidamount;
			$getuser->cancelamount = $getcancelamount;
			$getuser->unpaidamount = $getunpaidamount;
			$getuser->previousunpaid = $getpreviousunpaid;
			$getuser->previouscancel = $getpreviouscancelamount;
			$getuser->totalunpaidamount = $gettotalunpaidamount;
			$getuser->remainingunpaidamount = $getremainingunpaidamount;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('logoorder_date','=',$lists)
			->count('logoorder_id');
			$getdailyorderamount = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('logoorder_date','=',$lists)
			->sum('logoorder_amount');
			// dd($lists);
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			// $alldata[] = $getuser;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlycompleteorders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,11,17])
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlypendingorders =  DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlypaidorders = $getmonthlycompleteorders-$getmonthlypendingorders;

			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['paidorder'] = $getmonthlypaidorders;
			$ordercounts['pendingorder'] = $getmonthlypendingorders;

			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'User Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function userlogodashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
			$gettargetachieved = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17])
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');	
			$gettargetpaid = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');	
			$gettargetcancel = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');	
			$getunpaidamount = $gettargetachieved-$gettargetpaid-$gettargetcancel;
			$getuser->achieved = $gettargetachieved;
			$getuser->paid = $gettargetpaid;
			$getuser->unpaidamount = $getunpaidamount;
			$getuser->remaining = $getuser->user_target - $gettargetachieved;
			$getuser->perday = $getuser->user_target / $workingdays;
			// $alldata = $getuser;

			$list=array();
			$noofdays = date('t');

			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			// dd($list);
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('logoorder_date','=',$lists)
			->count('logoorder_id');
			$getdailyorderamount = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('logoorder_date','=',$lists)
			->sum('logoorder_amount');
			// dd($lists);
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			// $alldata[] = $getuser;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','!=',18)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');

			$getmonthlycompleteorders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,11,12,17])
			->where('created_by','=',$request->id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');

			$getmonthlypaidorders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('created_by','=',$request->id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');

			$gettargetcancel = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('logoorder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('logoorder_id');
			$getmonthlypendingorders = $getmonthlycompleteorders-$getmonthlypaidorders-$gettargetcancel;

			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['paidorder'] = $getmonthlypaidorders;
			$ordercounts['cancelorder'] = $gettargetcancel;
			$ordercounts['pendingorder'] = $getmonthlypendingorders;

			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
			// dd($daysRemaining);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'User Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function workerlogodashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getmonth = $request->usertarget_month;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('logoorder_workpickby','=',$request->id)
			->where('logoorder_date','=',$lists)
			->count('logoorder_id');
			$getdailyorderamount = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('logoorder_workpickby','=',$request->id)
			->where('logoorder_date','=',$lists)
			->sum('logoorder_amount');
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('logoorder_workpickby','=',$request->id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlycompleteorders = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('logoorder_workpickby','=',$request->id)
			->whereBetween('logoorder_date', [$from, $to])
			->where('orderstatus_id','>',4)
			->count('logoorder_id');
			$getmonthlyremainingorders = $getmonthlytotalnooforders-$getmonthlycompleteorders;
			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['pendingorder'] = $getmonthlyremainingorders;
			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'Worker Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function adminwebdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->first();
		$getcampaigntarget = DB::table('user')
		->select('user_target')
		->where('campaign_id','=',$request->campaign_id)
		->whereIn('role_id',[3,4])
		->where('status_id','=',1)
		->sum('user_target');
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$getgrosssale = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17,18])
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_amount');
			$getpaidamountmaseter = DB::table('weborder')
			->select('weborder_remainingamount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[11,18])
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_remainingamount');
			$getpaidamountmilestone = DB::table('weborderpayment')
			->select('weborderpayment_amount')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[11,18])
			->whereBetween('weborderpayment_date', [$from, $to])
			->sum('weborderpayment_amount');
			$getpaidamount = $getpaidamountmaseter+$getpaidamountmilestone;
			$getcancelamount = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_amount');
			$getunpaidamount = $getgrosssale-$getpaidamount-$getcancelamount;
			$getpreviousunpaidamountmaster = DB::table('weborder')
			->select('weborder_remainingamount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,12,17])
			->where('weborder_date','<', $from)
			->sum('weborder_remainingamount');
			$getpreviousunpaidamountmilestone = DB::table('weborderpayment')
			->select('weborderpayment_amount')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,12,17])
			->where('weborderpayment_date','<', $from)
			->sum('weborderpayment_amount');
			$getpreviousunpaidamount = $getpreviousunpaidamountmaster+$getpreviousunpaidamountmilestone;
			$getpreviouscancelamount = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->where('weborder_date','<', $from)
			->sum('weborder_amount');
			$getpreviousunpaid = $getpreviousunpaidamount-$getpreviouscancelamount;
			$gettotalunpaidamount = $getunpaidamount+$getpreviousunpaid;
			$getremainingunpaidamount = 0;

			$getuser->campaigntarget = $getcampaigntarget;
			$getuser->grosssale = $getgrosssale;
			$getuser->paidamount = $getpaidamount;
			$getuser->cancelamount = $getcancelamount;
			$getuser->unpaidamount = $getunpaidamount;
			$getuser->previousunpaid = $getpreviousunpaid;
			$getuser->previouscancel = $getpreviouscancelamount;
			$getuser->totalunpaidamount = $gettotalunpaidamount;
			$getuser->remainingunpaidamount = $getremainingunpaidamount;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('weborder_date','=',$lists)
			->count('weborder_id');
			$getdailyorderamount = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('weborder_date','=',$lists)
			->sum('weborder_amount');
			// dd($lists);
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			// $alldata[] = $getuser;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlycompleteorders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,11,17])
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlypendingorders =  DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlypaidorders = $getmonthlycompleteorders-$getmonthlypendingorders;

			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['paidorder'] = $getmonthlypaidorders;
			$ordercounts['pendingorder'] = $getmonthlypendingorders;

			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'User Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function userwebdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
			$gettargetachieved = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17])
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_amount');	
			$gettargetpaidmaster = DB::table('weborder')
			->select('weborder_remainingamount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_remainingamount');	
			$gettargetpaidmilestone = DB::table('webpaymentandorderdetail')
			->select('weborderpayment_amount')
			->where('status_id','=',1)
			->where('order_createdby','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('weborderpayment_date', [$from, $to])
			->sum('weborderpayment_amount');	
			$gettargetpaid = $gettargetpaidmaster+$gettargetpaidmilestone;
			$gettargetcancel = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_amount');	
			$getunpaidamount = $gettargetachieved-$gettargetpaid-$gettargetcancel;
			$getuser->achieved = $gettargetachieved;
			$getuser->paid = $gettargetpaid;
			$getuser->unpaidamount = $getunpaidamount;
			$getuser->remaining = $getuser->user_target - $gettargetachieved;
			$getuser->perday = $getuser->user_target / $workingdays;
			// $alldata = $getuser;

			$list=array();
			$noofdays = date('t');

			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			// dd($list);
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('weborder_date','=',$lists)
			->count('weborder_id');
			$getdailyorderamount = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('weborder_date','=',$lists)
			->sum('weborder_amount');
			// dd($lists);
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			// $alldata[] = $getuser;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','!=',18)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');

			$getmonthlycompleteorders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,11,12,17])
			->where('created_by','=',$request->id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');

			$getmonthlypaidorders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('created_by','=',$request->id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');

			$gettargetcancel = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('weborder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('weborder_id');
			$getmonthlypendingorders = $getmonthlycompleteorders-$getmonthlypaidorders-$gettargetcancel;

			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['paidorder'] = $getmonthlypaidorders;
			$ordercounts['cancelorder'] = $gettargetcancel;
			$ordercounts['pendingorder'] = $getmonthlypendingorders;

			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
			// dd($daysRemaining);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'User Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function workerwebdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getmonth = $request->usertarget_month;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('weborder_workpickby','=',$request->id)
			->where('weborder_date','=',$lists)
			->count('weborder_id');
			$getdailyorderamount = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('weborder_workpickby','=',$request->id)
			->where('weborder_date','=',$lists)
			->sum('weborder_amount');
			$datewiseordercount[$index]['nooforders'] = $getdailynooforders;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamount;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlytotalnooforders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('weborder_workpickby','=',$request->id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlycompleteorders = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('weborder_workpickby','=',$request->id)
			->whereBetween('weborder_date', [$from, $to])
			->where('orderstatus_id','>',4)
			->count('weborder_id');
			$getmonthlyremainingorders = $getmonthlytotalnooforders-$getmonthlycompleteorders;
			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforders;
			$ordercounts['completeorder'] = $getmonthlycompleteorders;
			$ordercounts['pendingorder'] = $getmonthlyremainingorders;
			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'Worker Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function adminlogowebdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->first();
		$getcampaigntarget = DB::table('user')
		->select('user_target')
		->where('campaign_id','=',$request->campaign_id)
		->whereIn('role_id',[3,4])
		->where('status_id','=',1)
		->sum('user_target');
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$getgrosssaleweb = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17,18])
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_amount');
			$getpaidamountmaseterweb = DB::table('weborder')
			->select('weborder_remainingamount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[11,18])
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_remainingamount');
			$getpaidamountmilestoneweb = DB::table('weborderpayment')
			->select('weborderpayment_amount')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[11,18])
			->whereBetween('weborderpayment_date', [$from, $to])
			->sum('weborderpayment_amount');
			$getpaidamountweb = $getpaidamountmaseterweb+$getpaidamountmilestoneweb;
			$getcancelamountweb = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_amount');
			$getunpaidamountweb = $getgrosssaleweb-$getpaidamountweb-$getcancelamountweb;
			$getpreviousunpaidamountmasterweb = DB::table('weborder')
			->select('weborder_remainingamount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,12,17])
			->where('weborder_date','<', $from)
			->sum('weborder_remainingamount');
			$getpreviousunpaidamountmilestoneweb = DB::table('weborderpayment')
			->select('weborderpayment_amount')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,12,17])
			->where('weborderpayment_date','<', $from)
			->sum('weborderpayment_amount');
			$getpreviousunpaidamountweb = $getpreviousunpaidamountmasterweb+$getpreviousunpaidamountmilestoneweb;
			$getpreviouscancelamountweb = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->where('weborder_date','<', $from)
			->sum('weborder_amount');
			$getpreviousunpaidweb = $getpreviousunpaidamountweb-$getpreviouscancelamountweb;
			$gettotalunpaidamountweb = $getunpaidamountweb+$getpreviousunpaidweb;
			$getremainingunpaidamountweb = 0;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynoofordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('weborder_date','=',$lists)
			->count('weborder_id');
			$getdailyorderamountweb = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('weborder_date','=',$lists)
			->sum('weborder_amount');
			$getdailynooforderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('logoorder_date','=',$lists)
			->count('logoorder_id');
			$getdailyorderamountlogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('logoorder_date','=',$lists)
			->sum('logoorder_amount');
			$datewiseordercount[$index]['nooforders'] = $getdailynooforderslogo+$getdailynoofordersweb;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamountlogo+$getdailyorderamountweb;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlytotalnoofordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlycompleteordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,11,17])
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlypendingordersweb =  DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlypaidordersweb = $getmonthlycompleteordersweb-$getmonthlypendingordersweb;
			// Logo Start
			$getgrosssalelogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,11,17,18])
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');
			$getpaidamountlogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[11,18])
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');
			$getcancelamountlogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');
			$getunpaidamountlogo = $getgrosssalelogo-$getpaidamountlogo-$getcancelamountlogo;
			$getpreviousunpaidamountlogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereIn('orderstatus_id',[7,8,9,10,12,17])
			->where('logoorder_date','<', $from)
			->sum('logoorder_amount');
			$getpreviouscancelamountlogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',12)
			->where('logoorder_date','<', $from)
			->sum('logoorder_amount');
			$getpreviousunpaidlogo = $getpreviousunpaidamountlogo-$getpreviouscancelamountlogo;
			$gettotalunpaidamountlogo = $getunpaidamountlogo+$getpreviousunpaidlogo;
			$getremainingunpaidamountlogo = 0;

			$getuser->campaigntarget = $getcampaigntarget;
			$getuser->grosssale = $getgrosssalelogo+$getgrosssaleweb;
			$getuser->paidamount = $getpaidamountlogo+$getpaidamountweb;
			$getuser->cancelamount = $getcancelamountlogo+$getcancelamountweb;
			$getuser->unpaidamount = $getunpaidamountlogo+$getunpaidamountweb;
			$getuser->previousunpaid = $getpreviousunpaidlogo+$getpreviousunpaidweb;
			$getuser->previouscancel = $getpreviouscancelamountlogo+$getpreviouscancelamountweb;
			$getuser->totalunpaidamount = $gettotalunpaidamountlogo+$gettotalunpaidamountweb;
			$getuser->remainingunpaidamount = $getremainingunpaidamountlogo+$getremainingunpaidamountweb;
			
			$getmonthlytotalnooforderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlycompleteorderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,11,17])
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlypendingorderslogo =  DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlypaidorderslogo = $getmonthlycompleteorderslogo-$getmonthlypendingorderslogo;

			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnooforderslogo+$getmonthlytotalnoofordersweb;
			$ordercounts['completeorder'] = $getmonthlycompleteorderslogo+$getmonthlycompleteordersweb;
			$ordercounts['paidorder'] = $getmonthlypaidorderslogo+$getmonthlypaidordersweb;
			$ordercounts['pendingorder'] = $getmonthlypendingorderslogo+$getmonthlypendingordersweb;

			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
			// Logo End
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'User Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function workerlogowebdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getmonth = $request->usertarget_month;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynoofordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('weborder_workpickby','=',$request->id)
			->where('weborder_date','=',$lists)
			->count('weborder_id');
			$getdailyorderamountweb = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('weborder_workpickby','=',$request->id)
			->where('weborder_date','=',$lists)
			->sum('weborder_amount');
			$getdailynooforderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('logoorder_workpickby','=',$request->id)
			->where('logoorder_date','=',$lists)
			->count('logoorder_id');
			$getdailyorderamountlogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('logoorder_workpickby','=',$request->id)
			->where('logoorder_date','=',$lists)
			->sum('logoorder_amount');
			$datewiseordercount[$index]['nooforders'] = $getdailynoofordersweb+$getdailynooforderslogo;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamountweb+$getdailyorderamountlogo;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlytotalnoofordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('weborder_workpickby','=',$request->id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlycompleteordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('weborder_workpickby','=',$request->id)
			->whereBetween('weborder_date', [$from, $to])
			->where('orderstatus_id','>',4)
			->count('weborder_id');
			$getmonthlyremainingordersweb = $getmonthlytotalnoofordersweb-$getmonthlycompleteordersweb;
			$getmonthlytotalnooforderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('logoorder_workpickby','=',$request->id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlycompleteorderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('logoorder_workpickby','=',$request->id)
			->whereBetween('logoorder_date', [$from, $to])
			->where('orderstatus_id','>',4)
			->count('logoorder_id');
			$getmonthlyremainingorderslogo = $getmonthlytotalnooforderslogo-$getmonthlycompleteorderslogo;
			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnoofordersweb+$getmonthlytotalnooforderslogo;
			$ordercounts['completeorder'] = $getmonthlycompleteordersweb+$getmonthlycompleteorderslogo;
			$ordercounts['pendingorder'] = $getmonthlyremainingordersweb+$getmonthlyremainingorderslogo;
			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'Worker Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function userlogowebdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
			$gettargetachievedweb = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17])
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_amount');	
			$gettargetpaidmasterweb = DB::table('weborder')
			->select('weborder_remainingamount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_remainingamount');	
			$gettargetpaidmilestoneweb = DB::table('webpaymentandorderdetail')
			->select('weborderpayment_amount')
			->where('status_id','=',1)
			->where('order_createdby','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('weborderpayment_date', [$from, $to])
			->sum('weborderpayment_amount');	
			$gettargetpaidweb = $gettargetpaidmasterweb+$gettargetpaidmilestoneweb;
			$gettargetcancelweb = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('weborder_date', [$from, $to])
			->sum('weborder_amount');	
			$getunpaidamountweb = $gettargetachievedweb-$gettargetpaidweb-$gettargetcancelweb;
			$getmonthlytotalnoofordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','!=',18)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlycompleteordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,11,12,17])
			->where('created_by','=',$request->id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$getmonthlypaidordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('created_by','=',$request->id)
			->whereBetween('weborder_date', [$from, $to])
			->count('weborder_id');
			$gettargetcancelweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('weborder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('weborder_id');
			$getmonthlypendingordersweb = $getmonthlycompleteordersweb-$getmonthlypaidordersweb-$gettargetcancelweb;
			$gettargetachievedlogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17])
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');	
			$gettargetpaidlogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',11)
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');	
			$gettargetcancellogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');	
			$getunpaidamountlogo = $gettargetachievedlogo-$gettargetpaidlogo-$gettargetcancellogo;
			$sumachieved = $gettargetachievedweb+$getunpaidamountlogo;
			$getuser->achieved = $gettargetachievedweb+$gettargetachievedlogo;
			$getuser->paid = $gettargetpaidweb+$gettargetpaidlogo;
			$getuser->unpaidamount = $getunpaidamountweb+$getunpaidamountlogo;
			$getuser->remaining = $getuser->user_target - $sumachieved;
			$getuser->perday = $getuser->user_target / $workingdays;
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('logoorder_date','=',$lists)
			->count('logoorder_id');
			$getdailyorderamountlogo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('logoorder_date','=',$lists)
			->sum('logoorder_amount');
			$getdailynoofordersweb = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('weborder_date','=',$lists)
			->count('weborder_id');
			$getdailyorderamountweb = DB::table('weborder')
			->select('weborder_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('weborder_date','=',$lists)
			->sum('weborder_amount');
			$datewiseordercount[$index]['nooforders'] = $getdailynooforderslogo+$getdailynoofordersweb;
			$datewiseordercount[$index]['orderamount'] = $getdailyorderamountlogo+$getdailyorderamountweb;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlytotalnooforderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','!=',18)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlycompleteorderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->whereIn('orderstatus_id',[7,8,9,10,11,12,17])
			->where('created_by','=',$request->id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$getmonthlypaidorderslogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',11)
			->where('created_by','=',$request->id)
			->whereBetween('logoorder_date', [$from, $to])
			->count('logoorder_id');
			$gettargetcancellogo = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('logoorder_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('logoorder_id');
			$getmonthlypendingorderslogo = $getmonthlycompleteorderslogo-$getmonthlypaidorderslogo-$gettargetcancellogo;
			$ordercounts = array();
			$ordercounts['totalorder'] = $getmonthlytotalnoofordersweb+$getmonthlytotalnooforderslogo;
			$ordercounts['completeorder'] = $getmonthlycompleteordersweb+$getmonthlycompleteorderslogo;
			$ordercounts['paidorder'] = $getmonthlypaidordersweb+$getmonthlypaidorderslogo;
			$ordercounts['cancelorder'] = $gettargetcancelweb+$gettargetcancellogo;
			$ordercounts['pendingorder'] = $getmonthlypendingordersweb+$getmonthlypendingorderslogo;
			$timestamp = strtotime(date('Y-m-d'));
			$daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $ordercounts, 'message' => 'User Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function dmeagentdailydeal(Request $request){
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
		$dealcount = array();
		$dealindex=0;
		foreach ($list as $lists) {
			$getalldeals = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->id)
			->whereNotIn('orderstatus_id',[1,12])
			->where('dmeclient_date','=',$lists)
			->count('dmeclient_id');
			$gethmodeals = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->id)
			->whereNotIn('orderstatus_id',[1,12])
			->where('dmeclient_date','=',$lists)
			->where('dmeclient_cardtype','=',"HMO")
			->count('dmeclient_id');
			$getppodeals = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->id)
			->whereNotIn('orderstatus_id',[1,12])
			->where('dmeclient_date','=',$lists)
			->where('dmeclient_cardtype','=',"PPO")
			->count('dmeclient_id');
			$getpartbinactivedeals = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->id)
			->whereNotIn('orderstatus_id',[1,12])
			->where('dmeclient_date','=',$lists)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->count('dmeclient_id');
			$getpartbactivedeals = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->id)
			->whereNotIn('orderstatus_id',[1,12])
			->where('dmeclient_date','=',$lists)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->count('dmeclient_id');
			$getmspdeals = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('created_by','=',$request->id)
			->whereNotIn('orderstatus_id',[1,12])
			->where('dmeclient_date','=',$lists)
			->where('dmeclient_cardtype','=',"MSP")
			->count('dmeclient_id');
			$dealcount[$dealindex]['getalldeals'] = $getalldeals;
			$dealcount[$dealindex]['gethmodeals'] = $gethmodeals;
			$dealcount[$dealindex]['getppodeals'] = $getppodeals;
			$dealcount[$dealindex]['getpartbinactivedeals'] = $getpartbinactivedeals;
			$dealcount[$dealindex]['getpartbactivedeals'] = $getpartbactivedeals;
			$dealcount[$dealindex]['getmspdeals'] = $getmspdeals;
			$dealcount[$dealindex]['date'] = $lists;
			$dealindex++;
		}
		return response()->json(['dealcount' => $dealcount, 'message' => 'Daily Employee Deal Count'],200);
	}
	public function cpamanagerdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforderspicked = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cpamanagerpickby','=',$request->id)
			->where('cpaorderstatus','=',null)
			->where('dmeclient_cpalastupdateddate','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersconnected = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cpamanagerpickby','=',$request->id)
			->where('cpaorderstatus','=',2)
			->where('dmeclient_cpalastupdateddate','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersdnc = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cpamanagerpickby','=',$request->id)
			->where('cpaorderstatus','=',1)
			->where('dmeclient_cpalastupdateddate','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersnoanswer = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cpamanagerpickby','=',$request->id)
			->where('cpaorderstatus','=',3)
			->where('dmeclient_cpalastupdateddate','=',$lists)
			->count('dmeclient_id');
			$datewiseordercount[$index]['dailynooforderspicked'] = $getdailynooforderspicked;
			$datewiseordercount[$index]['dailynoofordersconnected'] = $getdailynoofordersconnected;
			$datewiseordercount[$index]['dailynoofordersdnc'] = $getdailynoofordersdnc;
			$datewiseordercount[$index]['dailynoofordersnoanswer'] = $getdailynoofordersnoanswer;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlynooforderspicked = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cpamanagerpickby','=',$request->id)
			->where('cpaorderstatus','=',null)
			->whereBetween('dmeclient_cpalastupdateddate', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersconnected = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cpamanagerpickby','=',$request->id)
			->where('cpaorderstatus','=',2)
			->whereBetween('dmeclient_cpalastupdateddate', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersdnc = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cpamanagerpickby','=',$request->id)
			->where('cpaorderstatus','=',1)
			->whereBetween('dmeclient_cpalastupdateddate', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersnoanswer = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cpamanagerpickby','=',$request->id)
			->where('cpaorderstatus','=',3)
			->whereBetween('dmeclient_cpalastupdateddate', [$from, $to])
			->count('dmeclient_id');
			$monthlyordercounts = array();
			$monthlyordercounts['getmonthlynooforderspicked'] = $getmonthlynooforderspicked;
			$monthlyordercounts['getmonthlynoofordersconnected'] = $getmonthlynoofordersconnected;
			$monthlyordercounts['getmonthlynoofordersdnc'] = $getmonthlynoofordersdnc;
			$monthlyordercounts['getmonthlynoofordersnoanswer'] = $getmonthlynoofordersnoanswer;
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'dailyordercount' => $datewiseordercount, 'monthlyordercounts' => $monthlyordercounts, 'message' => 'CPA Manager Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function topagentsfordashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$gettopagent = DB::table('user')
		->select('user_id','user_name','user_picture','campaign_id')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$gettopagentcount = DB::table('user')
		->select('user_id')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->count('user_id');
		if ($gettopagentcount != 0) {
		$topagents = array();
		$index=0;
		foreach ($gettopagent as $gettopagents) {
		if ($request->campaign_id == 1) {
			$getagentorder = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->whereBetween('order_date', [$from, $to])
			->where('created_by','=',$gettopagents->user_id)
			->count('order_id');
		}elseif ($request->campaign_id == 2) {
			$getagentorder = DB::table('declient')
			->select('declient_id')
			->where('status_id','=',1)
			->where('created_by','=',$gettopagents->user_id)
			->count('declient_id');
		}elseif ($request->campaign_id == 3) {
			$getagentorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereBetween('dmeclient_date', [$from, $to])
			->where('created_by','=',$gettopagents->user_id)
			->count('dmeclient_id');
		}elseif ($request->campaign_id == 4) {
			$getagentorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereBetween('dmeclient_date', [$from, $to])
			->where('dmeclient_cpamanagerpickby','=',$gettopagents->user_id)
			->count('dmeclient_id');
		}elseif ($request->campaign_id == 5) {
			$getagentorder = DB::table('weborder')
			->select('weborder_id')
			->where('status_id','=',1)
			->whereBetween('dmeclient_date', [$from, $to])
			->where('created_by','=',$gettopagents->user_id)
			->count('weborder_id');
		}elseif ($request->campaign_id == 6) {
			$getagentorder = DB::table('logoorder')
			->select('logoorder_id')
			->where('status_id','=',1)
			->whereBetween('logoorder_date', [$from, $to])
			->where('created_by','=',$gettopagents->user_id)
			->count('logoorder_id');
		}else{
			$getagentorder = 0;
		}
			$gettopagent[$index]->noordercreated = $getagentorder;
			$topagents[$index] = $gettopagent;
			$index++;
		}
		$indexset=0;
		foreach ($topagents as $key => $row)
		{
			$top[$key]['noordercreated']  = $row[$indexset]->noordercreated;
		    $top[$key]['user_id']  = $row[$indexset]->user_id;
		    $top[$key]['user_name']  = $row[$indexset]->user_name;
		    $top[$key]['user_picture']  = $row[$indexset]->user_picture;
		    $indexset++;
		}    
		array_multisort($top, SORT_DESC, $topagents);
			return response()->json(['topagents' => $top, 'message' => 'Top Agents'],200);
		}else{
			$emptyarray = array();
			return response()->json(['topagents' => $emptyarray, 'message' => 'Top Agents'],200);
		}
	}
	public function cpaadmindashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getmonth = $request->usertarget_month;
		$getpreviousmonth = date("Y-m", strtotime("-1 months"));
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
			$list=array();
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailynooforderspicked = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',null)
			->where('dmeclient_cpalastupdateddate','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersconnected = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',2)
			->where('dmeclient_cpalastupdateddate','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersdnc = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',1)
			->where('dmeclient_cpalastupdateddate','=',$lists)
			->count('dmeclient_id');
			$getdailynoofordersnoanswer = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',3)
			->where('dmeclient_cpalastupdateddate','=',$lists)
			->count('dmeclient_id');
			$datewiseordercount[$index]['dailynooforderspicked'] = $getdailynooforderspicked;
			$datewiseordercount[$index]['dailynoofordersconnected'] = $getdailynoofordersconnected;
			$datewiseordercount[$index]['dailynoofordersdnc'] = $getdailynoofordersdnc;
			$datewiseordercount[$index]['dailynoofordersnoanswer'] = $getdailynoofordersnoanswer;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlynooforderspicked = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',null)
			->whereBetween('dmeclient_cpalastupdateddate', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersconnected = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',2)
			->whereBetween('dmeclient_cpalastupdateddate', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersdnc = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',1)
			->whereBetween('dmeclient_cpalastupdateddate', [$from, $to])
			->count('dmeclient_id');
			$getmonthlynoofordersnoanswer = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',3)
			->whereBetween('dmeclient_cpalastupdateddate', [$from, $to])
			->count('dmeclient_id');
			$monthlyordercounts = array();
			$monthlyordercounts['getmonthlynooforderspicked'] = $getmonthlynooforderspicked;
			$monthlyordercounts['getmonthlynoofordersconnected'] = $getmonthlynoofordersconnected;
			$monthlyordercounts['getmonthlynoofordersdnc'] = $getmonthlynoofordersdnc;
			$monthlyordercounts['getmonthlynoofordersnoanswer'] = $getmonthlynoofordersnoanswer;
			$gettodaynooforderspicked = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',null)
			->where('dmeclient_cpalastupdateddate','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordersconnected = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',2)
			->where('dmeclient_cpalastupdateddate','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordersdnc = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',1)
			->where('dmeclient_cpalastupdateddate','=',$to)
			->count('dmeclient_id');
			$gettodaynoofordersnoanswer = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('cpaorderstatus','=',3)
			->where('dmeclient_cpalastupdateddate','=',$to)
			->count('dmeclient_id');
			$todayordercounts = array();
			$todayordercounts['gettodaynooforderspicked'] = $gettodaynooforderspicked;
			$todayordercounts['gettodaynoofordersconnected'] = $gettodaynoofordersconnected;
			$todayordercounts['gettodaynoofordersdnc'] = $gettodaynoofordersdnc;
			$todayordercounts['gettodaynoofordersnoanswer'] = $gettodaynoofordersnoanswer;
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'dailyordercount' => $datewiseordercount, 'monthlyordercounts' => $monthlyordercounts, 'todayordercounts' => $todayordercounts, 'message' => 'CPA Admin Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deagentdashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'id'		  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$monthly = array();
		$from = $request->from;
		$to = $request->to;
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getsurrentmonth = date('m');
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
		$usertarget_month = date('Y-m');
			$gettotal = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getsave = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->where('deorderstatus_id','=',12)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getback = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->where('deorderstatus_id','=',9)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getcancel = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->where('deorderstatus_id','=',8)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getforwardedtomanager = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->whereIn('deorderstatus_id',[1,2,3,4,5,6,7,10,11,13,14,15])
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getqualified = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->whereIn('deorderstatus_id',[4,5,6,7,10,15])
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getnonqualified = $getforwardedtomanager - $getqualified;

			$monthly['total'] = $gettotal;
			$monthly['save'] = $getsave;
			$monthly['back'] = $getback;
			$monthly['cancel'] = $getcancel;
			$monthly['forwardedtomanager'] = $getforwardedtomanager;
			$monthly['qualified'] = $getqualified;
			$monthly['nonqualified'] = $getnonqualified;

			$list=array();
			$month = date('m');
			$year = date('Y');
			$noofdays = date('t');
			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailytotal = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->where('depayment_date','=', $lists)
			->groupBy('declient_id')
			->count();
			$getdailysave = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->where('deorderstatus_id','=',12)
			->where('depayment_date','=', $lists)
			->groupBy('declient_id')
			->count();
			$getdailyback = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->where('deorderstatus_id','=',9)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getdailycancel = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->where('deorderstatus_id','=',8)
			->where('depayment_date','=', $lists)
			->groupBy('declient_id')
			->count();
			$getdailyforwardedtomanager = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->whereIn('deorderstatus_id',[1,2,3,4,5,6,7,10,11,13,14,15])
			->where('depayment_date','=', $lists)
			->groupBy('declient_id')
			->count();
			$getdailyqualified = DB::table('depayment')
			->select('depayment_id')
			->where('created_by','=',$request->id)
			->whereIn('deorderstatus_id',[4,5,6,7,10,15])
			->where('depayment_date','=', $lists)
			->groupBy('declient_id')
			->count();
			$getdailynonqualified = $getforwardedtomanager - $getqualified;
			$datewiseordercount[$index]['total'] = $getdailytotal;
			$datewiseordercount[$index]['save'] = $getdailysave;
			$datewiseordercount[$index]['back'] = $getdailyback;
			$datewiseordercount[$index]['cancel'] = $getdailycancel;
			$datewiseordercount[$index]['forwardedtomanager'] = $getdailyforwardedtomanager;
			$datewiseordercount[$index]['qualified'] = $getdailyqualified;
			$datewiseordercount[$index]['nonqualified'] = $getdailynonqualified;
			$datewiseordercount[$index]['date'] = $lists;
			$index++;
			}
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'dailydeals' => $datewiseordercount, 'monthlydeals' => $monthly, 'message' => 'DE Agent Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function demanagerdashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'id'		  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$monthly = array();
		$from = $request->from;
		$to = $request->to;
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getsurrentmonth = date('m');
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
			$gettotal = DB::table('depayment')
			->select('depayment_id')
			->whereIn('deorderstatus_id',[1,2,3,4,5,6,7,8,9,10,11,13,14,15])
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getpick = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->whereIn('deorderstatus_id',[2,3,4,5,6,7,8,9,10,11,13,14,15])
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getsave = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',3)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getqualified = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',4)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getpickbybilling = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',5)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getsavebybilling = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',6)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getprocessed = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',7)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getcancel = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',8)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getbacktoagent = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',9)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getbacktomanager = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',10)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getnonqualified = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',11)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getsavebynonqualify = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',13)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getpickbynonqualify = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',14)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getnonprocess = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_managerpickby','=',$request->id)
			->where('deorderstatus_id','=',15)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			
			$monthly['total'] = $gettotal;
			$monthly['getpick'] = $getpick;
			$monthly['save'] = $getsave;
			$monthly['qualified'] = $getqualified;
			$monthly['pickbybilling'] = $getpickbybilling;
			$monthly['savebybilling'] = $getsavebybilling;
			$monthly['processed'] = $getprocessed;
			$monthly['cancel'] = $getcancel;
			$monthly['backtoagent'] = $getbacktoagent;
			$monthly['backtomanager'] = $getbacktomanager;
			$monthly['nonqualified'] = $getnonqualified;
			$monthly['savebynonqualify'] = $getsavebynonqualify;
			$monthly['pickbynonqualify'] = $getpickbynonqualify;
			$monthly['nonprocess'] = $getnonprocess;
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'monthlydeals' => $monthly, 'message' => 'DE Manager Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function debillingdashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'id'		  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$monthly = array();
		$from = $request->from;
		$to = $request->to;
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getsurrentmonth = date('m');
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
			$gettotal = DB::table('depayment')
			->select('depayment_id')
			->whereIn('deorderstatus_id',[4,5,6,7,15])
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getpick = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_billingpickby','=',$request->id)
			->whereIn('deorderstatus_id',[5,6,7,8,15])
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getsave = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_billingpickby','=',$request->id)
			->where('deorderstatus_id','=',6)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getprocess = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_billingpickby','=',$request->id)
			->where('deorderstatus_id','=',7)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getnonprocess = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_billingpickby','=',$request->id)
			->where('deorderstatus_id','=',15)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			$getcancel = DB::table('depayment')
			->select('depayment_id')
			->where('depayment_billingpickby','=',$request->id)
			->where('deorderstatus_id','=',8)
			->whereBetween('depayment_date', [$from, $to])
			->groupBy('declient_id')
			->count();
			
			$monthly['total'] = $gettotal;
			$monthly['getpick'] = $getpick;
			$monthly['save'] = $getsave;
			$monthly['process'] = $getprocess;
			$monthly['nonprocess'] = $getnonprocess;
			$monthly['cancel'] = $getcancel;
		if(isset($getuser)){
		return response()->json(['userdata' => $getuser, 'monthlydeals' => $monthly, 'message' => 'DE Billing Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function leadashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'to'  				=> 'required',
	    	'from'  			=> 'required',
	    	'id'		  		=> 'required',
	    	'campaign_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$from = $request->from;
		$to = $request->to;
		$getyearandmonth = explode('-', $to);
		$finalyearandmonth = $getyearandmonth[0].'-'.$getyearandmonth[1];
		$getfromyearandmonth = explode('-', $from);
		$year = $getfromyearandmonth[0];
		$month = $getfromyearandmonth[1];
		$getuser = DB::table('getuserdetails')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$getmonth = $request->usertarget_month;
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
		$list=array();
			$noofdays = date('t');

			for($d=1; $d<=$noofdays; $d++)
			{
			    $time=mktime(12, 0, 0, $month, $d, $year);          
			    if (date('m', $time)==$month)       
			        $list[]=date('Y-m-d', $time);
			}
			$datewiseordercount = array();
			$index = 0;
			foreach ($list as $lists) {
			$getdailysavelead = DB::table('freshlead')
			->select('freshlead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('freshlead_date','=',$lists)
			->where('campaign_id','=',$request->campaign_id)
			->count('freshlead_id');
			$getdailytotalorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('lead_date','=',$lists)
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$getdailyfreeorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('lead_amountquoted','=',0)
			->where('lead_date','=',$lists)
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$getdailypaidorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('lead_amountquoted','!=',0)
			->where('lead_date','=',$lists)
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$getdailyassignorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',4)
			->where('lead_date','=',$lists)
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$getdailycancelorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->where('lead_date','=',$lists)
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$datewiseordercount[$index]['savelead'] = $getdailysavelead;
			$datewiseordercount[$index]['totalorder'] = $getdailytotalorders;
			$datewiseordercount[$index]['freeorder'] = $getdailyfreeorders;
			$datewiseordercount[$index]['paidorder'] = $getdailypaidorders;
			$datewiseordercount[$index]['assignorder'] = $getdailyassignorders;
			$datewiseordercount[$index]['cancelorder'] = $getdailycancelorders;
			$datewiseordercount[$index]['orderdate'] = $lists;
			$index++;
			}
			$getmonthlysavelead = DB::table('freshlead')
			->select('freshlead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->whereBetween('freshlead_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('freshlead_id');
			$getmonthlytotalorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->whereBetween('lead_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$getmonthlyfreeorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('lead_amountquoted','=',0)
			->whereBetween('lead_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$getmonthlypaidorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('lead_amountquoted','!=',0)
			->whereBetween('lead_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$getmonthlyassignorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',4)
			->whereBetween('lead_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$getmonthlycancelorders = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderstatus_id','=',12)
			->whereBetween('lead_date', [$from, $to])
			->where('campaign_id','=',$request->campaign_id)
			->count('lead_id');
			$monthlyordercount = array();
			$monthlyordercount['savelead'] = $getmonthlysavelead;
			$monthlyordercount['totalorder'] = $getmonthlytotalorders;
			$monthlyordercount['freeorder'] = $getmonthlyfreeorders;
			$monthlyordercount['paidorder'] = $getmonthlypaidorders;
			$monthlyordercount['assignorder'] = $getmonthlyassignorders;
			$monthlyordercount['cancelorder'] = $getmonthlycancelorders;
		if(isset($getuser)){
			return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $monthlyordercount, 'message' => 'Lead Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function maxachieve(Request $request){
		$from = $request->from;
		$to = $request->to;
		$max = DB::table('order')
		->select('order_amountquoted')
		->where('status_id','=',1)
		->whereIn('orderstatus_id',[4,5,6,7,8,9,10,11,17,18])
		->whereBetween('order_date', [$from, $to])
		->sum('order_amountquoted');
		$web = DB::table('webpaymentandorderdetail')
		->select('weborderpayment_amount')
		->where('status_id','=',1)
		->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17,18])
		->whereBetween('weborderpayment_date', [$from, $to])
		->sum('weborderpayment_amount');	
		$logo = DB::table('logoorder')
		->select('logoorder_amount')
		->where('status_id','=',1)
		->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17,18])
		->whereBetween('logoorder_date', [$from, $to])
		->sum('logoorder_amount');
		$getgrosssale = $max+$web+$logo;
		if(isset($getgrosssale)){
		return response()->json($getgrosssale,200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function displayteamreport(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getuserdetails = array();
		$getuserlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->whereIn('role_id',[3,4])
		->where('status_id','=',1)
		->whereIn('campaign_id',[1,9])
		->get();
		$index=0;
		foreach ($getuserlist as $getuserlist) {
			if ($getuserlist->user_id == 3) {
				$globaluser_id = 169;
			}elseif ($getuserlist->user_id == 4){
				$globaluser_id = 170;
			}elseif ($getuserlist->user_id == 9) {
				$globaluser_id = 172;
			}elseif ($getuserlist->user_id == 178) {
				$globaluser_id = 0;
			}elseif ($getuserlist->user_id == 131) {
				$globaluser_id = 171;
			}elseif ($getuserlist->user_id == 179) {
				$globaluser_id = 0;
			}else{
				$globaluser_id = 0;
			}
			$targetincrement = DB::table('usertarget')
			->select('usertarget_target')
			->whereIn('user_id',[$getuserlist->user_id, $globaluser_id])
			->where('usertarget_month','<=',date('Y-m'))
			->where('status_id','=',1)
			->sum('usertarget_target');
			$usertarget = $getuserlist->user_target+$targetincrement;
			$max = DB::table('order')
			->select('order_amountquoted')
			->where('status_id','=',1)
			->whereIn('created_by',[$getuserlist->user_id, $globaluser_id])
			->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17,18])
			->whereBetween('order_date', [$from, $to])
			->sum('order_amountquoted');
			$web = DB::table('webpaymentandorderdetail')
			->select('weborderpayment_amount')
			->where('status_id','=',1)
			->whereIn('order_createdby',[$getuserlist->user_id, $globaluser_id])
			->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17,18])
			->whereBetween('weborderpayment_date', [$from, $to])
			->sum('weborderpayment_amount');	
			$logo = DB::table('logoorder')
			->select('logoorder_amount')
			->where('status_id','=',1)
			->whereIn('created_by',[$getuserlist->user_id, $globaluser_id])
			->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17,18])
			->whereBetween('logoorder_date', [$from, $to])
			->sum('logoorder_amount');	
			$achieved = $max+$web+$logo;
			$remaining = $usertarget-$achieved; 
			$getuserdetails[$index]['userid'] = $getuserlist->user_id;
			$getuserdetails[$index]['name'] = $getuserlist->user_name;
			$getuserdetails[$index]['picture'] = "/bizzcrm/public/userpicture/".$getuserlist->user_picture;
			$getuserdetails[$index]['target'] = $usertarget;
			$getuserdetails[$index]['achieved'] = $achieved;
			$getuserdetails[$index]['remaining'] = $remaining;
			$index++;
		}
		if(isset($getuserlist)){
			return response()->json([$getuserdetails],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function masterdashboard(Request $request){
		$from = $request->from;
		$to = $request->to;
		$maxachieve = DB::table('order')
		->select('order_amountquoted')
		->where('status_id','=',1)
		->where('campaign_id','=',1)
		->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17,18])
		->whereBetween('order_date', [$from, $to])
		->sum('order_amountquoted');
		$maxpaid = DB::table('order')
		->select('order_amountquoted')
		->where('status_id','=',1)
		->where('campaign_id','=',1)
		->where('orderstatus_id','=',11)
		->whereBetween('order_date', [$from, $to])
		->sum('order_amountquoted');
		$maxunpaid = $maxachieve-$maxpaid;

		$globalachieve = DB::table('order')
		->select('order_amountquoted')
		->where('status_id','=',1)
		->where('campaign_id','=',9)
		->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17,18])
		->whereBetween('order_date', [$from, $to])
		->sum('order_amountquoted');
		$globalpaid = DB::table('order')
		->select('order_amountquoted')
		->where('status_id','=',1)
		->where('campaign_id','=',9)
		->where('orderstatus_id','=',11)
		->whereBetween('order_date', [$from, $to])
		->sum('order_amountquoted');
		$globalunpaid = $globalachieve-$globalpaid;
		
		$webachieve = DB::table('webpaymentandorderdetail')
		->select('weborderpayment_amount')
		->where('status_id','=',1)
		->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17,18])
		->whereBetween('weborderpayment_date', [$from, $to])
		->sum('weborderpayment_amount');

		$webpaid = DB::table('webpaymentandorderdetail')
		->select('weborderpayment_amount')
		->where('status_id','=',1)
		->where('orderstatus_id','=',11)
		->whereBetween('weborderpayment_date', [$from, $to])
		->sum('weborderpayment_amount');
		$webunpaid = $webachieve-$webpaid;


		$logoachieve = DB::table('logoorder')
		->select('logoorder_amount')
		->where('status_id','=',1)
		->whereIn('orderstatus_id',[4,5,7,8,9,10,11,17,18])
		->whereBetween('logoorder_date', [$from, $to])
		->sum('logoorder_amount');
		$logopaid = DB::table('logoorder')
		->select('logoorder_amount')
		->where('status_id','=',1)
		->where('orderstatus_id','=',11)
		->whereBetween('logoorder_date', [$from, $to])
		->sum('logoorder_amount');
		$logounpaid = $logoachieve-$logopaid;
		
		$masterachieve = $maxachieve+$globalachieve+$webachieve+$logoachieve;
		$masterpaid = $maxpaid+$globalpaid+$webpaid+$logopaid;
		$masterunpaid = $maxunpaid+$globalunpaid+$webunpaid+$logounpaid;
		return response()->json(['maxachieve' => $maxachieve,'maxpaid' => $maxpaid,'maxunpaid' => $maxunpaid,'globalachieve' => $globalachieve,'globalpaid' => $globalpaid,'globalunpaid' => $globalunpaid,'webachieve' => $webachieve,'webpaid' => $webpaid,'webunpaid' => $webunpaid,'logoachieve' => $logoachieve,'logopaid' => $logopaid,'logounpaid' => $logounpaid,'masterachieve' => $masterachieve,'masterpaid' => $masterpaid,'masterunpaid' => $masterunpaid],200);
	}
}