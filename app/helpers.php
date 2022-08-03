<?php
    public function checktokentest($token){
        if (!empty($token)) {
        $validatetoken = DB::table('user')
        ->select('user.verify_token')
        ->where('verify_token','=',$token)
        ->where('status_id','=',1)
        ->first();
        if ($validatetoken) {    
            return;
        }else{
            return response()->json("Invalid Auth Token", 400);
        }
        }else{
            return response()->json("Auth Token Does not Exist", 400);
        }
    }