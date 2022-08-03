<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->user_id){
            $check = DB::table('user')
            ->select('user_email')
            ->where('user_id','=',$request->user_id)
            ->where('status_id','=',1)
            ->count();
            if ($check == 0) {
            return redirect('/login');   
            }else{
            return $next($request);
            }
        }else{
            return redirect('/login');
        }
    }
}
