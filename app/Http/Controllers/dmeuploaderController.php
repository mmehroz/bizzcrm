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

class dmeuploaderController extends Controller
{
	public function dmeuploader(Request $request)
	{
		$validate = Validator::make($request->all(), [ 
	    	'dmefile'  		=> 'required',
	    	'campaign_id'  	=> 'required', 
	    ]);
		if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$file = $request->file('dmefile');
		if ($file) {
			$filename = $file->getClientOriginalName();
			$extension = $file->getClientOriginalExtension(); //Get extension of uploaded file
			$tempPath = $file->getRealPath();
			$fileSize = $file->getSize(); //Get size of uploaded file in bytes
			//Check for file extension and size
			// $this->checkUploadedFileProperties($extension, $fileSize);
			$valid_extension = "csv"; //Only want csv and excel files
			$maxFileSize = 2097152; // Uploaded file size limit is 2mb
			if ($extension == $valid_extension) {
				if ($fileSize <= $maxFileSize) {
				//Where uploaded file will be stored on the server 
				$location = 'dmeuploads'; //Created an "uploads" folder for that
				// Upload file
				$file->move(public_path('dmeuploads/'),$filename);
				// In case the uploaded file path is to be stored in the database 
				$filepath = public_path($location . "/" . $filename);
				// Reading file
				$file = fopen($filepath, "r");
				$importData_arr = array(); // Read through the file and store the contents as an array
				$i = 0;
				//Read the contents of the uploaded file 
				while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
					$num = count($filedata);
					// Skip first row (Remove below comment if you want to skip the first row)
					if ($i == 0) {
						$i++;
						continue;
					}
					for ($c = 0; $c < $num; $c++) {
						$importData_arr[$i][] = $filedata[$c];
					}
						$i++;
				}
				fclose($file); //Close after reading
				$j = 0;
				foreach ($importData_arr as $importData) {
					try {
						$adds = array(
							'fullname'			=> $importData[0],
							'gender'			=> $importData[1],
							'dateofbirth'		=> $importData[2],
							'city'				=> $importData[3],
							'homephone'			=> $importData[4],
							'cellphone'			=> $importData[5],
							'address'			=> $importData[6],
							'zipcode'			=> $importData[7],
							'waistsize'			=> $importData[8],
							'height'			=> $importData[9],
							'weight'			=> $importData[10],
							'medicareid'		=> $importData[11],
							'doctorname'		=> $importData[12],
							'doctorhpone'		=> $importData[13],
							'doctorfaxnumber'	=> $importData[14],
							'doctornpi'			=> $importData[15],
							'doctoraddress'		=> $importData[16],
							'havediabetic'		=> $importData[17],
							'haveheartdisease'	=> $importData[18],
							'havecancer'		=> $importData[19],
							'takingmedication'	=> $importData[20],
							'causeofpain'		=> $importData[21],
							'rateyourpain'		=> $importData[22],
							'campaign_id'		=> $request->campaign_id,
							'status_id'			=> 1,
						);
					DB::table('dmerawdata')->insert($adds);
					} catch (\Exception $e) {
						DB::rollBack();
					}
				}
					return response()->json(['message' => 'Successfully Uploaded'],200);
				} else {
					return response()->json("File Size Too Large", 400);
				}
			} else {
					return response()->json("Invalid Format", 400);
			}
		} else {
				return response()->json("No file was uploaded Invalid Upload", 400);
		}
	}
	public function dmeuploadedlist(Request $request){
		$getlist = DB::table('dmerawdata')
		->select('*')
		->where('campaign_id','=',$request->campaign_id)
		->where('status_id','=',1)
		->orderBy('dmerawdata_id','DESC')
		->get();	
		if($getlist){
			return response()->json(['data' => $getlist, 'message' => 'DME Uploaded List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
}