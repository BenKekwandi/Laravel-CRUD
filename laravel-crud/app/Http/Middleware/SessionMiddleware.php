<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rules\Exists;

class SessionMiddleware
{
 
    public function handle(Request $request, Closure $next): Response
    {
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || strpos($authorizationHeader, 'Bearer ') !== 0) {
            return response()->json([
                'message' => 'Invalid or missing Authorization header'
            ], 401);
        }

        $accessToken = explode("Bearer ", $authorizationHeader)[1];

        if(!Redis::exists("user:session:$accessToken"))
        {
            return response()->json([
                'message' => 'Failed to authenticate the user',
                'data'=> Redis::get("user:session:$accessToken")
            ], 401);
        }

        $tokenData = json_decode(Redis::get("user:session:$accessToken"), true);
        $currentTime =time();

        if($currentTime >= intval($tokenData['expired_at']))
        {
            return response()->json([
                'message' => 'Authentication failed, Token already expired',
                'data'=> Redis::get("user:session:$accessToken")
            ], 401);
        }

        // return response()->json([
        //     "message"=>"Successfull Authentication",
        //     'token' => $accessToken,
        //     'tokenData'=>$tokenData,
        //     "currentTime"=> $currentTime,
        //     'expiration'=>$tokenData['expired_at']
        // ], 200);

        return $next($request);

    }
}
