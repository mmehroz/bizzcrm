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

class dmecommissionController extends Controller
{
	public function adddmecommission(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'commission'  	=> 'required',
	    	'r_id'			=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$multiple = $request->commission;
		foreach ($multiple as $multiples) {
		$addcommission[] = array(
		'dmecommission_startfrom'	=> $multiples['dmecommission_startfrom'],
		'dmecommission_rate'		=> $multiples['dmecommission_rate'],
		'dmecommission_type'		=> $multiples['dmecommission_type'],
		'role_id' 					=> $request->r_id,
		'status_id'		 			=> 1,
		'created_by'	 			=> $request->user_id,
		'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		}
		DB::table('dmecommission')->insert($addcommission);
		if($addcommission){
			return response()->json(['message' => 'Commission Added Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function dmecommissionlist(Request $request){
		$getcommissionlist = DB::table('dmecommission')
		->select('*')
		->where('status_id','=',1)
		->get();
		if($getcommissionlist){
			return response()->json(['commissiondata' => $getcommissionlist, 'message' => 'Commission List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function dmecommissionreport(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'id'	  		=> 'required',
	    	'campaign_id'	=> 'required',
	    	'date'			=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getyearandmonth = explode('-', $request->date);
		$year = $getyearandmonth[0];
		$month = $getyearandmonth[1];
		$date = $year."-".$month."-01";
		$firstdate = date("$year-$month-01");
		$lastdate = date("Y-m-t", strtotime($firstdate));
		$monthlycommissiondata = array();
		$weeklycommissiondata = array();
		$commissiondata =  array();
		$weekdate = array();
		$list=array();
		$commissionindex=0;
		$weeklycommissionindex=0;
		$dailycommisionamount;
		$dailyapprovedorders;
		$monthlycommisionamount;
		$monthlyapprovedorders;
		$getrole = DB::table('user')
		->select('role_id')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
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
		for($d=1; $d<=31; $d++)
		{
		    $time=mktime(12, 0, 0, $month, $d, $year);          
		    if (date('m', $time)==$month)       
		    $list[]=date('Y-m-d', $time);
		}
		$getdailycommission = DB::table('dmecommission')
		->select('*')
		->where('role_id','=',$getrole->role_id)
		->where('dmecommission_type','=',"Daily")
		->where('status_id','=',1)
		->first();
		if (isset($getdailycommission)) {
			$dailycommissionrate = $getdailycommission->dmecommission_rate;
			$dailycommissionstart = $getdailycommission->dmecommission_startfrom;
		}else{
			$dailycommissionrate = 0;
			$dailycommissionstart = 0;
		}
		foreach ($list as $lists) {
		$getdailyapproved = DB::table('dmeclient')
		->select('dmeclient_id')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->where('created_by','=',$request->id)
		->whereIn('dmeclient_cardtype',["Medicare part B active","PPO"])
		->where('dmeclient_date','=',$lists)
		->count('dmeclient_id');
		if ($getdailyapproved >= $dailycommissionstart) {
			$dailycommisionamount = $dailycommissionrate*$getdailyapproved;
			$dailyapprovedorders = $getdailyapproved;
		}else{
			$dailycommisionamount = 0;
			$dailyapprovedorders = 0;
		}
			$commissiondata[$commissionindex]['dailycommisionamount'] = $dailycommisionamount;
			$commissiondata[$commissionindex]['dailyapprovedorders'] = $dailyapprovedorders;
			$commissiondata[$commissionindex]['date'] = $lists;
			$commissionindex++;
		}
		$getweeklycommission = DB::table('dmecommission')
		->select('*')
		->where('role_id','=',$getrole->role_id)
		->where('dmecommission_type','=',"Weekly")
		->where('status_id','=',1)
		->first();
		if (isset($getweeklycommission)) {
			$weeklycommissionrate = $getweeklycommission->dmecommission_rate;
			$weeklycommissionstart = $getweeklycommission->dmecommission_rate;
		}else{
			$weeklycommissionrate = 0;
			$weeklycommissionstart = 0;
		}
		foreach ($weekdate as $weekdates) {
		$getweeklyapproved = DB::table('dmeclient')
		->select('dmeclient_id')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->where('created_by','=',$request->id)
		->whereIn('dmeclient_cardtype',["Medicare part B active","PPO"])
		->whereBetween('dmeclient_date', [$weekdates['startdate'], $weekdates['enddate']])
		->count('dmeclient_id');
		if ($getweeklyapproved >= $weeklycommissionstart) {
			$weeklycommisionamount = $weeklycommissionrate;
			$weeklyapprovedorders = $getweeklyapproved;
		}else{
			$weeklycommisionamount = 0;
			$weeklyapprovedorders = 0;
		}
		$weeklycommissiondata[$weeklycommissionindex]['weeklycommisionamount'] = $weeklycommisionamount;
		$weeklycommissiondata[$weeklycommissionindex]['weeklyapprovedorders'] = $weeklyapprovedorders;
		$weeklycommissiondata[$weeklycommissionindex]['weekstartdate'] = $weekdates['startdate'];
		$weeklycommissiondata[$weeklycommissionindex]['weekenddate'] = $weekdates['enddate'];
		$weeklycommissionindex++;
		}
		$getmonthlycommission = DB::table('dmecommission')
		->select('*')
		->where('role_id','=',$getrole->role_id)
		->where('dmecommission_type','=',"Monthly")
		->where('status_id','=',1)
		->first();
		if (isset($getmonthlycommission)) {
			$monthlycommissionrate = $getmonthlycommission->dmecommission_rate;
			$monthlycommissionstart = $getmonthlycommission->dmecommission_startfrom;
		}else{
			$monthlycommissionrate = 0;
			$monthlycommissionstart = 0;
		}
		$getmonthlyapproved = DB::table('dmeclient')
		->select('dmeclient_id')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->where('created_by','=',$request->id)
		->whereIn('dmeclient_cardtype',["Medicare part B active","PPO"])
		->whereBetween('dmeclient_date', [$firstdate, $lastdate])
		->count('dmeclient_id');
		if ($getmonthlyapproved >= $monthlycommissionstart) {
			$monthlycommisionamount = $monthlycommissionrate;
			$monthlyapprovedorders = $getmonthlyapproved;
		}else{
			$monthlycommisionamount = 0;
			$monthlyapprovedorders = 0;
		}
		$monthlycommissiondata['monthlycommisionamount'] = $monthlycommisionamount;
		$monthlycommissiondata['monthlyapprovedorders'] = $monthlyapprovedorders;
		return response()->json(['dailycommissiondata' => $commissiondata, 'weeklycommissiondata' => $weeklycommissiondata, 'monthlycommissiondata' => $monthlycommissiondata,'message' => 'Commission Report'],200);
	}
	public function dmecommissioncalculator(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'targetamount'	=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getrole = DB::table('user')
		->select('role_id')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->first();
		$getdailycommission = DB::table('dmecommission')
		->select('*')
		->where('role_id','=',$getrole->role_id)
		->where('dmecommission_type','=',"Daily")
		->where('status_id','=',1)
		->first();
		if (isset($getdailycommission)) {
		$calculatedcommission = $request->targetamount/$getdailycommission->dmecommission_rate;
			return response()->json(['calculatedcommission' => $calculatedcommission, 'message' => 'Commission Report'],200);
		}else{
			$calculatedcommission = 0;
			return response()->json(['calculatedcommission' => $calculatedcommission,'message' => 'Commission Report'],200);
		}
	}
	// bizz world commission
	public function dmebwcommissionreport(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'id'	  		=> 'required',
	    	'campaign_id'	=> 'required',
	    	'date'			=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$getyearandmonth = explode('-', $request->date);
		$year = $getyearandmonth[0];
		$month = $getyearandmonth[1];
		$date = $year."-".$month."-01";
		$firstdate = date("$year-$month-01");
		$lastdate = date("Y-m-t", strtotime($firstdate));
		$weeklycommissiondata = array();
		$commissiondata =  array();
		$weekdate = array();
		$list=array();
		$commissionindex=0;
		$weeklycommissionindex=0;
		$dailycommisionamount;
		$dailyapprovedorders;
		$getrole = DB::table('user')
		->select('role_id')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
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
		for($d=1; $d<=31; $d++)
		{
		    $time=mktime(12, 0, 0, $month, $d, $year);          
		    if (date('m', $time)==$month)       
		    $list[]=date('Y-m-d', $time);
		}
		if ($getrole->role_id == 4) {
		$getdailycommission = DB::table('dmecommission')
		->select('*')
		->where('role_id','=',$getrole->role_id)
		->where('dmecommission_type','=',"Daily")
		->where('status_id','=',1)
		->first();
		if (isset($getdailycommission)) {
			$dailycommissionrate = $getdailycommission->dmecommission_rate;
			$dailycommissionstart = $getdailycommission->dmecommission_startfrom;
		}else{
			$dailycommissionrate = 0;
			$dailycommissionstart = 0;
		}
		foreach ($list as $lists) {
		$getdailyapproved = DB::table('dmeclient')
		->select('dmeclient_id')
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->where('created_by','=',$request->id)
		->whereIn('dmeclient_cardtype',["Medicare part B active","PPO"])
		->where('dmeclient_date','=',$lists)
		->count('dmeclient_id');
		if ($getdailyapproved >= $dailycommissionstart) {
			$dailycommisionamount = $dailycommissionrate*$getdailyapproved;
			$dailyapprovedorders = $getdailyapproved;
		}else{
			$dailycommisionamount = 0;
			$dailyapprovedorders = 0;
		}
			$commissiondata[$commissionindex]['dailycommisionamount'] = $dailycommisionamount;
			$commissiondata[$commissionindex]['dailyapprovedorders'] = $dailyapprovedorders;
			$commissiondata[$commissionindex]['date'] = $lists;
			$commissionindex++;
		}
			return response()->json(['dailycommissiondata' => $commissiondata,'message' => 'Commission Report'],200);
		}elseif ($getrole->role_id == 3) {
			$weeklycommisionamount = array();
			$weeklyapprovedorders = array();
			foreach ($weekdate as $weekdates) {
			$getweeklycommission = DB::table('dmecommission')
			->select('*')
			->where('role_id','=',$getrole->role_id)
			->where('dmecommission_type','=',"Weekly")
			->where('status_id','=',1)
			->get();
			$cindex = 0;
			$commission = array();
			foreach ($getweeklycommission as $getweeklycommissions) {
				$commission[$cindex]['rate'] = $getweeklycommissions->dmecommission_rate;
				$commission[$cindex]['from'] = $getweeklycommissions->dmecommission_startfrom;
				$cindex++;
			}
			$slepindex=0;
			foreach ($commission as $commissions) {
			$getexperienceusers = DB::table('user')
			->select('user_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->count('user_id');
			$getweeklyapproved = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',28)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->whereBetween('dmeclient_date', [$weekdates['startdate'], $weekdates['enddate']])
			->count('dmeclient_id');
			$requiredleadsforfirstslep = $getexperienceusers-2*5;
			$getdealdifference = $requiredleadsforfirstslep-$getweeklyapproved;
			$requiredleadsforsecondslep = $requiredleadsforfirstslep/2;
			$remainingleaddorsecondslep = $getdealdifference-2*5;
			if ($slepindex == 0) {
			if ($getweeklyapproved >= $requiredleadsforfirstslep && $getexperienceusers > $commissions['from']) {
				$weeklycommisionamount[$slepindex] = $commissions['rate'];
				$weeklyapprovedorders[$slepindex] = $getweeklyapproved;
			}else{
				$weeklycommisionamount[$slepindex] = 0;
				$weeklyapprovedorders[$slepindex] = 0;
			}
			}else{
				if ($requiredleadsforsecondslep >= $remainingleaddorsecondslep && $requiredleadsforsecondslep > 0) {
					$weeklycommisionamount[$slepindex] = $commissions['rate'];
					$weeklyapprovedorders[$slepindex] = $getweeklyapproved;
				}else{
					$weeklycommisionamount[$slepindex] = 0;
					$weeklyapprovedorders[$slepindex] = 0;
				}
			}
			$slepindex++;
			}
			$weeklycommissiondata[$weeklycommissionindex]['weeklycommisionamount'] = $weeklycommisionamount;
			$weeklycommissiondata[$weeklycommissionindex]['weeklyapprovedorders'] = $weeklyapprovedorders;
			$weeklycommissiondata[$weeklycommissionindex]['weekstartdate'] = $weekdates['startdate'];
			$weeklycommissiondata[$weeklycommissionindex]['weekenddate'] = $weekdates['enddate'];
			$weeklycommissionindex++;
			}
			return response()->json(['weeklycommissiondata' => $weeklycommissiondata,'message' => 'Commission Report'],200);
		}elseif ($getrole->role_id == 2) {
			$weeklycommisionamount = array();
			$weeklyapprovedorders = array();
			foreach ($weekdate as $weekdates) {
			$getweeklycommission = DB::table('dmecommission')
			->select('*')
			->where('role_id','=',$getrole->role_id)
			->where('dmecommission_type','=',"Weekly")
			->where('status_id','=',1)
			->get();
			$cindex = 0;
			$commission = array();
			foreach ($getweeklycommission as $getweeklycommissions) {
				$commission[$cindex]['rate'] = $getweeklycommissions->dmecommission_rate;
				$commission[$cindex]['from'] = $getweeklycommissions->dmecommission_startfrom;
				$commission[$cindex]['client'] = $getweeklycommissions->dmemerchant_id;
				$cindex++;
			}
			$slepindex=0;
			foreach ($commission as $commissions) {
			$getweeklyapproved = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('orderstatus_id','=',28)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->whereBetween('dmeclient_date', [$weekdates['startdate'], $weekdates['enddate']])
			->count('dmeclient_id');
			$getspecialweeklyapproved = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$commissions['client'])
			->where('dmemerchant_id','=',28)
			->where('orderstatus_id','=',28)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->whereBetween('dmeclient_date', [$weekdates['startdate'], $weekdates['enddate']])
			->count('dmeclient_id');
			if ($slepindex == 0) {
				if ($getweeklyapproved >= $commissions['from']) {
					$weeklycommisionamount[$slepindex] = $commissions['rate'];
					$weeklyapprovedorders[$slepindex] = $getweeklyapproved;
				}else{
					$weeklycommisionamount[$slepindex] = 0;
					$weeklyapprovedorders[$slepindex] = 0;
				}
			}else{
				if ($getspecialweeklyapproved >= $commissions['from']) {
					$weeklycommisionamount[$slepindex] = $commissions['rate']*$getspecialweeklyapproved;
					$weeklyapprovedorders[$slepindex] = $getspecialweeklyapproved;
				}else{
					$weeklycommisionamount[$slepindex] = 0;
					$weeklyapprovedorders[$slepindex] = 0;
				}
			}
			$slepindex++;
			}
			$weeklycommissiondata[$weeklycommissionindex]['weeklycommisionamount'] = $weeklycommisionamount;
			$weeklycommissiondata[$weeklycommissionindex]['weeklyapprovedorders'] = $weeklyapprovedorders;
			$weeklycommissiondata[$weeklycommissionindex]['weekstartdate'] = $weekdates['startdate'];
			$weeklycommissiondata[$weeklycommissionindex]['weekenddate'] = $weekdates['enddate'];
			$weeklycommissionindex++;
			}
				return response()->json(['weeklycommissiondata' => $weeklycommissiondata,'message' => 'Commission Report'],200);
			}else{
				return response()->json(['message' => 'Commission Not Found'],400);
			}
	}
	public function dmeperformancereport(Request $request){
		$from = $request->from;
		$to = $request->to;
		$getagentlist = DB::table('user')
		->select('user_id','role_id','user_name','user_picture')
		->where('role_id','=',4)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getmanagerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_picture')
		->where('role_id','=',3)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$sortmanager = array();
		$getdoctorlist = DB::table('user')
		->select('user_id','role_id','user_name','user_picture')
		->where('role_id','=',10)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getbillinglist = DB::table('user')
		->select('user_id','role_id','user_name','user_picture')
		->where('role_id','=',2)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getleadlist = DB::table('user')
		->select('user_id','role_id','user_name','user_picture')
		->where('role_id','=',11)
		->where('status_id','=',1)
		->where('campaign_id','=',$request->campaign_id)
		->get();
		$getagentdetails = array();
		$getmanagerdetails = array();
		$getdoctordetails = array();
		$getbillingdetails = array();
		$getleaddetails = array();
		$agentindex=0;
		foreach ($getagentlist as $getagentlist) {
			$gettotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$getagentlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getsaveorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',1)
			->where('created_by','=',$getagentlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getforwardedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',2)
			->where('created_by','=',$getagentlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getppoorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"PPO")
			->where('created_by','=',$getagentlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getpartborder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->where('created_by','=',$getagentlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getotherorder = $getforwardedorder-$getppoorder-$getpartborder;

			$getagentlist->totalorder = $gettotalorder;
			$getagentlist->saveorder = $getsaveorder;
			$getagentlist->forwardedorder = $getforwardedorder;
			$getagentlist->ppoorder = $getppoorder;
			$getagentlist->partborder = $getpartborder;
			$getagentlist->otherorder = $getotherorder;
			$getagentdetails[$agentindex] = $getagentlist;
			$agentindex++;
		}
		$managerindex=0;
		foreach ($getmanagerlist as $getmanagerlist) {
			$sortmanager[] = $getmanagerlist->user_id;
			$gettotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',2)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getpickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',3)
			->where('dmeclient_managerpickby','=',$getmanagerlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getsubmitorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',20)
			->where('dmeclient_managerpickby','=',$getmanagerlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getforwardedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->where('dmeclient_managerpickby','=',$getmanagerlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getppoorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"PPO")
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->where('dmeclient_managerpickby','=',$getmanagerlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getpartborder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->where('dmeclient_managerpickby','=',$getmanagerlist->user_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getreturntoagentorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','=',6)
			->where('dmeclient_managerpickby','=',$getmanagerlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getreturntomanagerorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_managerpickby','=',$getmanagerlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->where('dmeclient_isreturnfromchase','=',1)
			->orwhere('dmeclient_isreturnfrombilling','=',1)
			->where('dmeclient_managerpickby','=',$getmanagerlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			
			$getmanagerlist->totalorder = $gettotalorder;
			$getmanagerlist->pickorder = $getpickorder;
			$getmanagerlist->submitorder = $getsubmitorder;
			$getmanagerlist->forwardedorder = $getforwardedorder;
			$getmanagerlist->ppoorder = $getppoorder;
			$getmanagerlist->partborder = $getpartborder;
			$getmanagerlist->returntoagentorder = $getreturntoagentorder;
			$getmanagerlist->returntomanagerorder = $getreturntomanagerorder;
			$getmanagerdetails[$managerindex] = $getmanagerlist;
			$managerindex++;
		}
		$doctorindex=0;
		foreach ($getdoctorlist as $getdoctorlist) {
			$gettotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereIn('dmeclient_managerpickby',$sortmanager)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getpickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$getdoctorlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$getdoctorlist->user_id)
			->where('dmeotherdetails_chase','=',"yes")
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getnonchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_chaseby','=',$getdoctorlist->user_id)
			->where('dmeotherdetails_chase','=',"no")
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getreturntomanagerorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_isreturnfromchase','=',1)
			->where('dmeclient_chaseby','=',$getdoctorlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getcreatedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$getdoctorlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			
			$getdoctorlist->totalorder = $gettotalorder;
			$getdoctorlist->pickorder = $getpickorder;
			$getdoctorlist->chaseorder = $getchaseorder;
			$getdoctorlist->nonchaseorder = $getnonchaseorder;
			$getdoctorlist->returntomanagerorder = $getreturntomanagerorder;
			$getdoctorlist->createdorder = $getcreatedorder;
			$getdoctordetails[$doctorindex] = $getdoctorlist;
			$doctorindex++;
		}
		$billingindex=0;
		foreach ($getbillinglist as $getbillinglist) {
			$gettotalorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->whereNotIn('orderstatus_id',[1,2,3,4,5,6,7,20])
			->whereIn('dmeclient_managerpickby',$sortmanager)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getpickorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$getbillinglist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getprocessorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$getbillinglist->user_id)
			->where('dmeclient_isprocess','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getnonchaseorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_billingby','=',$getbillinglist->user_id)
			->where('dmeclient_isnonprocess','=',1)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getreturntomanagerorder = DB::table('dmeorderdetails')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_isreturnfrombilling','=',1)
			->where('dmeclient_billingby','=',$getbillinglist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getcreatedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('created_by','=',$getbillinglist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			
			$getbillinglist->totalorder = $gettotalorder;
			$getbillinglist->pickorder = $getpickorder;
			$getbillinglist->processorder = $getprocessorder;
			$getbillinglist->nonchaseorder = $getnonchaseorder;
			$getbillinglist->returntomanagerorder = $getreturntomanagerorder;
			$getbillinglist->createdorder = $getcreatedorder;
			$getbillingdetails[$billingindex] = $getbillinglist;
			$billingindex++;
		}
		$leadindex=0;
		foreach ($getleadlist as $getleadlist) {
			$getforwardedorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>=',2)
			->where('dmeclient_teamleadid','=',$getleadlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getppoorder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"PPO")
			->where('dmeclient_teamleadid','=',$getleadlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getpartborder = DB::table('dmeclient')
			->select('dmeclient_id')
			->where('status_id','=',1)
			->where('dmeclient_cardtype','=',"Medicare part B active")
			->where('dmeclient_teamleadid','=',$getmanagerlist->user_id)
			->where('campaign_id','=',$request->campaign_id)
			->whereBetween('dmeclient_date', [$from, $to])
			->count();
			$getotherorder = $getforwardedorder-$getppoorder-$getpartborder;
			
			$getleadlist->forwardedorder = $getforwardedorder;
			$getleadlist->ppoorder = $getppoorder;
			$getleadlist->partborder = $getpartborder;
			$getleadlist->otherorder = $getotherorder;
			$getleaddetails[$leadindex] = $getleadlist;
			$leadindex++;
		}
		if(isset($getagentlist)){
		return response()->json(['agentdetails' => $getagentdetails ,'managerdetails' => $getmanagerdetails,'doctordetails' => $getdoctordetails,'billingdetails' => $getbillingdetails ,'leaddetails' => $getleaddetails,'message' => 'Monthly Employee Performance Report'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}