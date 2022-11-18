<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\crmLoginController;
use App\Http\Controllers\campaignController;
use App\Http\Controllers\userController;
use App\Http\Controllers\clientController;
use App\Http\Controllers\orderController;	
use App\Http\Controllers\chatController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\decrmController;
use App\Http\Controllers\targetController;
use App\Http\Controllers\reportController;
use App\Http\Controllers\weborderController;
use App\Http\Controllers\logoorderController;
use App\Http\Controllers\dmecrmController;
use App\Http\Controllers\cpacrmController;
use App\Http\Controllers\merchantController;
use App\Http\Controllers\dmecommissionController;
use App\Http\Controllers\dmeuploaderController;
use App\Http\Controllers\denonqualifyController;
use App\Http\Controllers\demerchantController;
use App\Http\Controllers\leadController;
use App\Http\Controllers\billingmerchantController;
/*
|---------------------------------------------------------------------	-----
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::any('/login', [crmLoginController::class, 'login']);
Route::middleware('login.check')->group(function(){	
Route::any('/logout', [crmLoginController::class, 'logout']);
Route::any('/role', [crmLoginController::class, 'role']);
Route::any('/locationAndCurrency', [crmLoginController::class, 'locationAndCurrency']);

Route::any('/campaigntype', [campaignController::class, 'campaigntype']);
Route::any('/createcampaign', [campaignController::class, 'createcampaign']);
Route::any('/updatecampaign', [campaignController::class, 'updatecampaign']);
Route::any('/campaignlist', [campaignController::class, 'campaignlist']);
Route::any('/campaigndetails', [campaignController::class, 'campaigndetails']);
Route::any('/deletecampaign', [campaignController::class, 'deletecampaign']);
Route::any('/stateslist', [campaignController::class, 'stateslist']);
Route::any('/addpost', [campaignController::class, 'addpost']);
Route::any('/showpost', [campaignController::class, 'showpost']);
Route::any('/weekdates', [campaignController::class, 'weekdates']);
Route::any('/updatepaymentduedate', [campaignController::class, 'updatepaymentduedate']);

Route::any('/createuser', [userController::class, 'createuser']);
Route::any('/updateuser', [userController::class, 'updateuser']);
Route::any('/userlist', [userController::class, 'userlist']);
Route::any('/userdetails', [userController::class, 'userdetails']);
Route::any('/deleteuser', [userController::class, 'deleteuser']);
Route::any('/uploadcoverpicture', [userController::class, 'uploadcoverpicture']);

Route::any('/createclient', [clientController::class, 'createclient']);
Route::any('/updateclient', [clientController::class, 'updateclient']);
Route::any('/clientlist', [clientController::class, 'clientlist']);
Route::any('/clientdetails', [clientController::class, 'clientdetails']);
Route::any('/deleteclient', [clientController::class, 'deleteclient']);
Route::any('/clientprofile', [clientController::class, 'clientprofile']);
Route::any('/importrawclient', [clientController::class, 'importrawclient']);
Route::any('/transferclient', [clientController::class, 'transferclient']);
Route::any('/lockclient', [clientController::class, 'lockclient']);
Route::any('/unlockclient', [clientController::class, 'unlockclient']);

Route::any('/senddate', [orderController::class, 'senddate']);
Route::any('/createorder', [orderController::class, 'createorder']);
Route::any('/updateorder', [orderController::class, 'updateorder']);
Route::any('/deallist', [orderController::class, 'deallist']);
Route::any('/managerunpaiddeallist', [orderController::class, 'managerunpaiddeallist']);
Route::any('/managerforwardedunpaiddeallist', [orderController::class, 'managerforwardedunpaiddeallist']);
Route::any('/managerpaiddeallist', [orderController::class, 'managerpaiddeallist']);
Route::any('/managerforwardedpaiddeallist', [orderController::class, 'managerforwardedpaiddeallist']);
Route::any('/managercanceldeallist', [orderController::class, 'managercanceldeallist']);
Route::any('/managerforwardedcanceldeallist', [orderController::class, 'managerforwardedcanceldeallist']);
Route::any('/managerrecoverydeallist', [orderController::class, 'managerrecoverydeallist']);
Route::any('/managerforwardedrecoverydeallist', [orderController::class, 'managerforwardedrecoverydeallist']);
Route::any('/deletedeal', [orderController::class, 'deletedeal']);
Route::any('/orderlist', [orderController::class, 'orderlist']);
Route::any('/cancelorderlist', [orderController::class, 'cancelorderlist']);
Route::any('/mergecancelorderlist', [orderController::class, 'mergecancelorderlist']);
Route::any('/orderdetails', [orderController::class, 'orderdetails']);
Route::any('/updateorderstatusagent', [orderController::class, 'updateorderstatusagent']);
Route::any('/updateorderstatusworker', [orderController::class, 'updateorderstatusworker']);
Route::any('/updateorderstatusmanager', [orderController::class, 'updateorderstatusmanager']);
Route::any('/updateorderstatusbilling', [orderController::class, 'updateorderstatusbilling']);
Route::any('/deleteorder', [orderController::class, 'deleteorder']);
Route::any('/deleteattachment', [orderController::class, 'deleteattachment']);
Route::any('/designerlist', [orderController::class, 'designerlist']);
Route::any('/digitizerlist', [orderController::class, 'digitizerlist']);
Route::any('/randomsearchclient', [orderController::class, 'randomsearchclient']);
Route::any('/saveorcancilsearchclient', [orderController::class, 'saveorcancilsearchclient']);
Route::any('/saveorcancilclientlist', [orderController::class, 'saveorcancilclientlist']);
Route::any('/forwardeddeallist', [orderController::class, 'forwardeddeallist']);
Route::any('/pickdeallist', [orderController::class, 'pickdeallist']);
Route::any('/workorderlist', [orderController::class, 'workorderlist']);
Route::any('/updateorderstatutoedit', [orderController::class, 'updateorderstatutoedit']);
Route::any('/editorderlist', [orderController::class, 'editorderlist']);
Route::any('/unpickorder', [orderController::class, 'unpickorder']);
Route::any('/pickorder', [orderController::class, 'pickorder']);
Route::any('/completedorderlist', [orderController::class, 'completedorderlist']);
Route::any('/scrumboardforwardeddetails', [orderController::class, 'scrumboardforwardeddetails']);
Route::any('/scrumboardassigneddetails', [orderController::class, 'scrumboardassigneddetails']);
Route::any('/scrumboardompleteddetails', [orderController::class, 'scrumboardompleteddetails']);
Route::any('/scrumboardsentdetails', [orderController::class, 'scrumboardsentdetails']);
Route::any('/scrumboardbillingdetails', [orderController::class, 'scrumboardbillingdetails']);
Route::any('/scrumboardpaiddetails', [orderController::class, 'scrumboardpaiddetails']);
Route::any('/scrumboardeditdetails', [orderController::class, 'scrumboardeditdetails']);
Route::any('/billingdetails', [orderController::class, 'billingdetails']);
Route::any('/assignorder', [orderController::class, 'assignorder']);
Route::any('/notesonclientedit', [orderController::class, 'notesonclientedit']);
Route::any('/sentordertoclient', [orderController::class, 'sentordertoclient']);
Route::any('/billingforwardeddeallist', [orderController::class, 'billingforwardeddeallist']);
Route::any('/billingpickdeallist', [orderController::class, 'billingpickdeallist']);
Route::any('/invoicedeallist', [orderController::class, 'invoicedeallist']);
Route::any('/paiddeallist', [orderController::class, 'paiddeallist']);
Route::any('/canceldeallist', [orderController::class, 'canceldeallist']);
Route::any('/recoverydeallist', [orderController::class, 'recoverydeallist']);
Route::any('/mergedealforbilling', [orderController::class, 'mergedealforbilling']);
Route::any('/mergedeallist', [orderController::class, 'mergedeallist']);
Route::any('/mergeorderlist', [orderController::class, 'mergeorderlist']);
Route::any('/mergeinvoicedeallist', [orderController::class, 'mergeinvoicedeallist']);
Route::any('/mergepaiddeallist', [orderController::class, 'mergepaiddeallist']);
Route::any('/mergecanceldeallist', [orderController::class, 'mergecanceldeallist']);
Route::any('/mergerecoverydeallist', [orderController::class, 'mergerecoverydeallist']);
Route::any('/cancelorder', [orderController::class, 'cancelorder']);
Route::any('/canceldeal', [orderController::class, 'canceldeal']);
Route::any('/savefollowup', [orderController::class, 'savefollowup']);
Route::any('/getfollowup', [orderController::class, 'getfollowup']);
Route::any('/updatepaypalinvoicenumber', [orderController::class, 'updatepaypalinvoicenumber']);
Route::any('/dealamount', [orderController::class, 'dealamount']);
Route::any('/mergedealamount', [orderController::class, 'mergedealamount']);
Route::any('/unmergedeal', [orderController::class, 'unmergedeal']);

Route::any('/sendMessage', [chatController::class, 'sendMessage']);
Route::any('/fetchMessage', [chatController::class, 'fetchMessage']);
Route::any('/getContactsUser', [chatController::class, 'getContactsUser']);
Route::any('/getContactsTotal', [chatController::class, 'getContactsTotal']);
Route::any('/searchUser', [chatController::class, 'searchUser']);
Route::any('/download', [chatController::class, 'download']);
Route::any('/makeSeen', [chatController::class, 'makeSeen']);
Route::any('/unseen', [chatController::class, 'unseen']);
Route::any('/fetchMessageGroup', [chatController::class, 'fetchMessageGroup']);
Route::any('/getUserGroups', [chatController::class, 'getUserGroups']);
Route::any('/getAllGroups', [chatController::class, 'getAllGroups']);
Route::any('/createGroup', [chatController::class, 'createGroup']);
Route::any('/updateGroup', [chatController::class, 'updateGroup']);
Route::any('/archiveGroup', [chatController::class, 'archiveGroup']);
Route::any('/addmember', [chatController::class, 'addmember']);
Route::any('/removemember', [chatController::class, 'removemember']);

Route::any('/admindashboard', [dashboardController::class, 'admindashboard']);
Route::any('/userdashboard', [dashboardController::class, 'userdashboard']);
Route::any('/agentlistfordashboard', [dashboardController::class, 'agentlistfordashboard']);
Route::any('/admincampaigndashboard', [dashboardController::class, 'admincampaigndashboard']);
Route::any('/billingdashboard', [dashboardController::class, 'billingdashboard']);
Route::any('/workerdashboard', [dashboardController::class, 'workerdashboard']);
Route::any('/dmedashboard', [dashboardController::class, 'dmedashboard']);
Route::any('/dmeagentdashboard', [dashboardController::class, 'dmeagentdashboard']);
Route::any('/dmemanagerdashboard', [dashboardController::class, 'dmemanagerdashboard']);
Route::any('/dmebillingdashboard', [dashboardController::class, 'dmebillingdashboard']);
Route::any('/dmedoctorchasedashboard', [dashboardController::class, 'dmedoctorchasedashboard']);
Route::any('/dmeagentdailydeal', [dashboardController::class, 'dmeagentdailydeal']);
Route::any('/adminlogodashboard', [dashboardController::class, 'adminlogodashboard']);
Route::any('/userlogodashboard', [dashboardController::class, 'userlogodashboard']);
Route::any('/workerlogodashboard', [dashboardController::class, 'workerlogodashboard']);
Route::any('/adminwebdashboard', [dashboardController::class, 'adminwebdashboard']);
Route::any('/userwebdashboard', [dashboardController::class, 'userwebdashboard']);
Route::any('/workerwebdashboard', [dashboardController::class, 'workerwebdashboard']);
Route::any('/adminlogowebdashboard', [dashboardController::class, 'adminlogowebdashboard']);
Route::any('/workerlogowebdashboard', [dashboardController::class, 'workerlogowebdashboard']);
Route::any('/userlogowebdashboard', [dashboardController::class, 'userlogowebdashboard']);
Route::any('/cpamanagerdashboard', [dashboardController::class, 'cpamanagerdashboard']);
Route::any('/topagentsfordashboard', [dashboardController::class, 'topagentsfordashboard']);
Route::any('/dmeadmindashboard', [dashboardController::class, 'dmeadmindashboard']);
Route::any('/cpaadmindashboard', [dashboardController::class, 'cpaadmindashboard']);
Route::any('/leadashboard', [dashboardController::class, 'leadashboard']);
Route::any('/masterdashboard', [dashboardController::class, 'masterdashboard']);

Route::any('/maxachieve', [dashboardController::class, 'maxachieve']);
Route::any('/displayteamreport', [dashboardController::class, 'displayteamreport']);

Route::any('/addtarget', [targetController::class, 'addtarget']);
Route::any('/updatetarget', [targetController::class, 'updatetarget']);
Route::any('/targetlist', [targetController::class, 'targetlist']);
Route::any('/usertargetlist', [targetController::class, 'usertargetlist']);

Route::any('/addcommission', [targetController::class, 'addcommission']);
Route::any('/commissionlist', [targetController::class, 'commissionlist']);

Route::any('/monthlytargetreport', [reportController::class, 'monthlytargetreport']);
Route::any('/commissionreport', [reportController::class, 'commissionreport']);

Route::any('/decheckclient', [decrmController::class, 'decheckclient']);
Route::any('/decreatedeal', [decrmController::class, 'decreatedeal']);
Route::any('/deupdatedeal', [decrmController::class, 'deupdatedeal']);
Route::any('/dedeallist', [decrmController::class, 'dedeallist']);
Route::any('/deforwardeddeallist', [decrmController::class, 'deforwardeddeallist']);
Route::any('/depickdeallist', [decrmController::class, 'depickdeallist']);
Route::any('/depickdeal', [decrmController::class, 'depickdeal']);
Route::any('/deunpickdeal', [decrmController::class, 'deunpickdeal']);
Route::any('/dedealdetails', [decrmController::class, 'dedealdetails']);
Route::any('/declientlist', [decrmController::class, 'declientlist']);
Route::any('/declientdetails', [decrmController::class, 'declientdetails']);
Route::any('/agentdealsformanager', [decrmController::class, 'agentdealsformanager']);
Route::any('/updatedeorderstatus', [decrmController::class, 'updatedeorderstatus']);
Route::any('/deremovecard', [decrmController::class, 'deremovecard']);

Route::any('/deagentdashboard', [dashboardController::class, 'deagentdashboard']);
Route::any('/demanagerdashboard', [dashboardController::class, 'demanagerdashboard']);
Route::any('/debillingdashboard', [dashboardController::class, 'debillingdashboard']);

Route::any('/createweborder', [weborderController::class, 'createweborder']);
Route::any('/weborderlist', [weborderController::class, 'weborderlist']);
Route::any('/managerforwardedweborderlist', [weborderController::class, 'managerforwardedweborderlist']);
Route::any('/managerpickweborderlist', [weborderController::class, 'managerpickweborderlist']);
Route::any('/unpickweborder', [weborderController::class, 'unpickweborder']);
Route::any('/pickweborder', [weborderController::class, 'pickweborder']);
Route::any('/weborderdetails', [weborderController::class, 'weborderdetails']);
Route::any('/updateweborder', [weborderController::class, 'updateweborder']);
Route::any('/updateweborderstatus', [weborderController::class, 'updateweborderstatus']);
Route::any('/workerforwardedweborderlist', [weborderController::class, 'workerforwardedweborderlist']);
Route::any('/workerpickweborderlist', [weborderController::class, 'workerpickweborderlist']);
Route::any('/workerweborderlist', [weborderController::class, 'workerweborderlist']);
Route::any('/submitweborderwork', [weborderController::class, 'submitweborderwork']);
Route::any('/managerforwardedunpaidweborderlist', [weborderController::class, 'managerforwardedunpaidweborderlist']);
Route::any('/managerunpaidweborderlist', [weborderController::class, 'managerunpaidweborderlist']);
Route::any('/managerforwardedpaidweborderlist', [weborderController::class, 'managerforwardedpaidweborderlist']);
Route::any('/managerpaidweborderlist', [weborderController::class, 'managerpaidweborderlist']);
Route::any('/managerforwardedcancelweborderlist', [weborderController::class, 'managerforwardedcancelweborderlist']);
Route::any('/managercancelweborderlist', [weborderController::class, 'managercancelweborderlist']);
Route::any('/billingforwardedweborderlist', [weborderController::class, 'billingforwardedweborderlist']);
Route::any('/billingpickweborderlist', [weborderController::class, 'billingpickweborderlist']);
Route::any('/billingunpaidweborderlist', [weborderController::class, 'billingunpaidweborderlist']);
Route::any('/billingpaidweborderlist', [weborderController::class, 'billingpaidweborderlist']);
Route::any('/billingrecoveryweborderlist', [weborderController::class, 'billingrecoveryweborderlist']);
Route::any('/billingcancelweborderlist', [weborderController::class, 'billingcancelweborderlist']);
Route::any('/billingsentwebordreinvoice', [weborderController::class, 'billingsentwebordreinvoice']);
Route::any('/updatewebpaypalinvoicenumber', [weborderController::class, 'updatewebpaypalinvoicenumber']);
Route::any('/requestweborderpayment', [weborderController::class, 'requestweborderpayment']);
Route::any('/updateweborderpaymentstatus', [weborderController::class, 'updateweborderpaymentstatus']);
Route::any('/billingsentwebordrepaymentinvoice', [weborderController::class, 'billingsentwebordrepaymentinvoice']);
Route::any('/updatewebpaymentpaypalinvoicenumber', [weborderController::class, 'updatewebpaymentpaypalinvoicenumber']);
Route::any('/webpaymentlist', [weborderController::class, 'webpaymentlist']);

Route::any('/logocategory', [logoorderController::class, 'logocategory']);
Route::any('/createlogoorder', [logoorderController::class, 'createlogoorder']);
Route::any('/logoorderlist', [logoorderController::class, 'logoorderlist']);
Route::any('/managerforwardedlogoorderlist', [logoorderController::class, 'managerforwardedlogoorderlist']);
Route::any('/managerpicklogoorderlist', [logoorderController::class, 'managerpicklogoorderlist']);
Route::any('/unpicklogoorder', [logoorderController::class, 'unpicklogoorder']);
Route::any('/picklogoorder', [logoorderController::class, 'picklogoorder']);
Route::any('/logoorderdetails', [logoorderController::class, 'logoorderdetails']);
Route::any('/updatelogoorder', [logoorderController::class, 'updatelogoorder']);
Route::any('/updatelogoorderstatus', [logoorderController::class, 'updatelogoorderstatus']);
Route::any('/workerforwardedlogoorderlist', [logoorderController::class, 'workerforwardedlogoorderlist']);
Route::any('/workerpicklogoorderlist', [logoorderController::class, 'workerpicklogoorderlist']);
Route::any('/workerlogoorderlist', [logoorderController::class, 'workerlogoorderlist']);
Route::any('/submitlogoorderwork', [logoorderController::class, 'submitlogoorderwork']);
Route::any('/managerforwardedunpaidlogoorderlist', [logoorderController::class, 'managerforwardedunpaidlogoorderlist']);
Route::any('/managerunpaidlogoorderlist', [logoorderController::class, 'managerunpaidlogoorderlist']);
Route::any('/managerforwardedpaidlogoorderlist', [logoorderController::class, 'managerforwardedpaidlogoorderlist']);
Route::any('/managerpaidlogoorderlist', [logoorderController::class, 'managerpaidlogoorderlist']);
Route::any('/managerforwardedcancellogoorderlist', [logoorderController::class, 'managerforwardedcancellogoorderlist']);
Route::any('/managercancellogoorderlist', [logoorderController::class, 'managercancellogoorderlist']);
Route::any('/billingforwardedlogoorderlist', [logoorderController::class, 'billingforwardedlogoorderlist']);
Route::any('/billingpicklogoorderlist', [logoorderController::class, 'billingpicklogoorderlist']);
Route::any('/billingunpaidlogoorderlist', [logoorderController::class, 'billingunpaidlogoorderlist']);
Route::any('/billingpaidlogoorderlist', [logoorderController::class, 'billingpaidlogoorderlist']);
Route::any('/billingrecoverylogoorderlist', [logoorderController::class, 'billingrecoverylogoorderlist']);
Route::any('/billingcancellogoorderlist', [logoorderController::class, 'billingcancellogoorderlist']);
Route::any('/billingsentlogoordreinvoice', [logoorderController::class, 'billingsentlogoordreinvoice']);
Route::any('/updatelogopaypalinvoicenumber', [logoorderController::class, 'updatelogopaypalinvoicenumber']);

Route::any('/newlead', [clientController::class, 'newlead']);

Route::any('/createdmeoorder', [dmecrmController::class, 'createdmeoorder']);
Route::any('/dmeorderlist', [dmecrmController::class, 'dmeorderlist']);
Route::any('/dmesaveorderlist', [dmecrmController::class, 'dmesaveorderlist']);
Route::any('/dmebillingforwardedorderlist', [dmecrmController::class, 'dmebillingforwardedorderlist']);
Route::any('/updatedmeorderstatus', [dmecrmController::class, 'updatedmeorderstatus']);
Route::any('/dmepaidorderlist', [dmecrmController::class, 'dmepaidorderlist']);
Route::any('/dmecancelorderlist', [dmecrmController::class, 'dmecancelorderlist']);
Route::any('/dmeapprovedorderlist', [dmecrmController::class, 'dmeapprovedorderlist']);
Route::any('/dmemanagerforwardedorderlist', [dmecrmController::class, 'dmemanagerforwardedorderlist']);
Route::any('/dmemanagerpickorderlist', [dmecrmController::class, 'dmemanagerpickorderlist']);
Route::any('/unpickdmeorder', [dmecrmController::class, 'unpickdmeorder']);
Route::any('/pickdmeorder', [dmecrmController::class, 'pickdmeorder']);
Route::any('/dmeorderdetails', [dmecrmController::class, 'dmeorderdetails']);
Route::any('/updatedmeoorder', [dmecrmController::class, 'updatedmeoorder']);
Route::any('/dmemanagerforwardedagentorderlist', [dmecrmController::class, 'dmemanagerforwardedagentorderlist']);
Route::any('/dmemanagerforwardedcancelorderlist', [dmecrmController::class, 'dmemanagerforwardedcancelorderlist']);
Route::any('/dmemanagercancelorderlist', [dmecrmController::class, 'dmemanagercancelorderlist']);
Route::any('/dmemanagerforwardedsubmitedorderlist', [dmecrmController::class, 'dmemanagerforwardedsubmitedorderlist']);
Route::any('/dmemanagersubmitedorderlist', [dmecrmController::class, 'dmemanagersubmitedorderlist']);
Route::any('/dmemanagerforwardedbillingorderlist', [dmecrmController::class, 'dmemanagerforwardedbillingorderlist']);
Route::any('/dmemanagerbillingorderlist', [dmecrmController::class, 'dmemanagerbillingorderlist']);
Route::any('/dmebillingpickorderlist', [dmecrmController::class, 'dmebillingpickorderlist']);
Route::any('/dmemanagerforwardedapproveorderlist', [dmecrmController::class, 'dmemanagerforwardedapproveorderlist']);
Route::any('/dmemanagerapproveorderlist', [dmecrmController::class, 'dmemanagerapproveorderlist']);
Route::any('/dmedoctorchaseorderlist', [dmecrmController::class, 'dmedoctorchaseorderlist']);
Route::any('/dmedoctorchaseyesorderlist', [dmecrmController::class, 'dmedoctorchaseyesorderlist']);
Route::any('/dmedoctorchasenoorderlist', [dmecrmController::class, 'dmedoctorchasenoorderlist']);
Route::any('/dmeclientlist', [dmecrmController::class, 'dmeclientlist']);
Route::any('/dmenpiclientlist', [dmecrmController::class, 'dmenpiclientlist']);
Route::any('/dmeclientorders', [dmecrmController::class, 'dmeclientorders']);
Route::any('/dmedoctorchasepickorderlist', [dmecrmController::class, 'dmedoctorchasepickorderlist']);
Route::any('/dmefilledorderlist', [dmecrmController::class, 'dmefilledorderlist']);
Route::any('/dmeprocessedorderlist', [dmecrmController::class, 'dmeprocessedorderlist']);
Route::any('/dmenonprocessedorderlist', [dmecrmController::class, 'dmenonprocessedorderlist']);
Route::any('/dmearchivedorderlist', [dmecrmController::class, 'dmearchivedorderlist']);
Route::any('/dmereturntomanagerorderlist', [dmecrmController::class, 'dmereturntomanagerorderlist']);
Route::any('/dmedoctornpilist', [dmecrmController::class, 'dmedoctornpilist']);
Route::any('/dmealldeal', [dmecrmController::class, 'dmealldeal']);
Route::any('/dmedoctorchasecnpvorderlist', [dmecrmController::class, 'dmedoctorchasecnpvorderlist']);
Route::any('/updatedmeorderfaxno', [dmecrmController::class, 'updatedmeorderfaxno']);
Route::any('/uploaddmeattachment', [dmecrmController::class, 'uploaddmeattachment']);
Route::any('/activatedmeorder', [dmecrmController::class, 'activatedmeorder']);
Route::any('/dmeservices', [dmecrmController::class, 'dmeservices']);
Route::any('/dmebraces', [dmecrmController::class, 'dmebraces']);
Route::any('/updatedmeordercardtype', [dmecrmController::class, 'updatedmeordercardtype']);
Route::any('/dmemyclientlist', [dmecrmController::class, 'dmemyclientlist']);
Route::any('/dmeverifyemail', [dmecrmController::class, 'dmeverifyemail']);
Route::any('/dmecancelorder', [dmecrmController::class, 'dmecancelorder']);
Route::any('/dmesavefollowup', [dmecrmController::class, 'dmesavefollowup']);
Route::any('/dmegetfollowup', [dmecrmController::class, 'dmegetfollowup']);
Route::any('/dmereturntoprocessororderlist', [dmecrmController::class, 'dmereturntoprocessororderlist']);

Route::any('/cpamanagerforwardedorderlist', [cpacrmController::class, 'cpamanagerforwardedorderlist']);
Route::any('/cpamanagerpickorderlist', [cpacrmController::class, 'cpamanagerpickorderlist']);
Route::any('/unpickcpaorder', [cpacrmController::class, 'unpickcpaorder']);
Route::any('/pickcpaorder', [cpacrmController::class, 'pickcpaorder']);
Route::any('/updatecpaorderstatus', [cpacrmController::class, 'updatecpaorderstatus']);
Route::any('/cpastatuswiseorderlist', [cpacrmController::class, 'cpastatuswiseorderlist']);

Route::any('/dmemerchantlist', [merchantController::class, 'dmemerchantlist']);
Route::any('/addmerchant', [merchantController::class, 'addmerchant']);
Route::any('/updatemerchant', [merchantController::class, 'updatemerchant']);
Route::any('/dmemerchantchecker', [merchantController::class, 'dmemerchantchecker']);
Route::any('/dmemerchantservices', [merchantController::class, 'dmemerchantservices']);
Route::any('/dmemerchantdetails', [merchantController::class, 'dmemerchantdetails']);
Route::any('/dmeupdatemerchantactivestatus', [merchantController::class, 'dmeupdatemerchantactivestatus']);

Route::any('/adddemerchant', [demerchantController::class, 'adddemerchant']);
Route::any('/demerchantlist', [demerchantController::class, 'demerchantlist']);
Route::any('/updatedemerchant', [demerchantController::class, 'updatedemerchant']);
Route::any('/deupdatemerchantactivestatus', [demerchantController::class, 'deupdatemerchantactivestatus']);

Route::any('/adddmecommission', [dmecommissionController::class, 'adddmecommission']);
Route::any('/dmecommissionlist', [dmecommissionController::class, 'dmecommissionlist']);
Route::any('/dmecommissionreport', [dmecommissionController::class, 'dmecommissionreport']);
Route::any('/dmecommissioncalculator', [dmecommissionController::class, 'dmecommissioncalculator']);
Route::any('/dmecommissionreport', [dmecommissionController::class, 'dmecommissionreport']);
Route::any('/dmebwcommissionreport', [dmecommissionController::class, 'dmebwcommissionreport']);
Route::any('/dmeperformancereport', [dmecommissionController::class, 'dmeperformancereport']);

Route::any('/dmeuploader',[dmeuploaderController::class,'dmeuploader']);
Route::any('/dmeuploadedlist',[dmeuploaderController::class,'dmeuploadedlist']);

Route::any('/deforwardednonqualifieddeal', [denonqualifyController::class, 'deforwardednonqualifieddeal']);
Route::any('/depickedstatuswisedeal', [denonqualifyController::class, 'depickedstatuswisedeal']);

Route::any('/depickedstatuswisedeal', [denonqualifyController::class, 'depickedstatuswisedeal']);
Route::any('/depickedstatuswisedeal', [denonqualifyController::class, 'depickedstatuswisedeal']);

Route::any('/createlead', [leadController::class, 'createlead']);
Route::any('/updatelead', [leadController::class, 'updatelead']);
Route::any('/leadlist', [leadController::class, 'leadlist']);
Route::any('/pickleadlist', [leadController::class, 'pickleadlist']);
Route::any('/leaddetails', [leadController::class, 'leaddetails']);
Route::any('/pickleadorder', [leadController::class, 'pickleadorder']);
Route::any('/unpickleadorder', [leadController::class, 'unpickleadorder']);
Route::any('/processlead', [leadController::class, 'processlead']);
Route::any('/savelead', [leadController::class, 'savelead']);
Route::any('/saveleadlist', [leadController::class, 'saveleadlist']);
Route::any('/saveleadfollowup', [leadController::class, 'saveleadfollowup']);
Route::any('/getleadfollowup', [leadController::class, 'getleadfollowup']);

Route::any('/addbillingmerchant', [billingmerchantController::class, 'addbillingmerchant']);
Route::any('/updatebillingmerchant', [billingmerchantController::class, 'updatebillingmerchant']);
Route::any('/billingmerchantlist', [billingmerchantController::class, 'billingmerchantlist']);
Route::any('/billingmerchantdetails', [billingmerchantController::class, 'billingmerchantdetails']);
Route::any('/deletebillingmerchant', [billingmerchantController::class, 'deletebillingmerchant']);
});

Route::get('/pusherauth', function (Request $request) {
	$userID = 1;
        $beamsToken = $beamsClient->generateToken($userID);
        return response()->json($beamsToken);
});