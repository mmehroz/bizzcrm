<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\RemoveGroupMember;
use App\Events\AddGroupMember;
use App\Models\User;
use App\Models\Message;
use App\Models\Group;
use App\Models\GroupMessage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class evchargeController extends Controller
{
	public function websocket(){
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $connection =  @socket_connect($socket, 'ws://103.133.133.19', 8080);

        if( $connection ){
            echo 'ONLINE';
        }
        else {
            echo 'OFFLINE: ' . socket_strerror(socket_last_error( $socket ));
        }

        // $a = socket_write($socket, 'AAAA');
        // var_dump($a);
    }
   
    // Functions Use In Controller End
}