<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {

            $user = User::create([
                'name'=> $request->input('name'),
                'email'=> $request->input('email'),
                'password'=> Hash::make($request->input('password')),
            ]);

            $response = [
                'user' => $user
            ];

            return response($response, 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Add a new User', 'error' => $e->getMessage()], 500);
        }
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $credentials = request(['email','password']);

        if(!Auth::attempt($credentials))
        {
            return response()->json([
                'message' => 'Failed to log in'
                ],401);
        }

        $user = $request->user();
        $token = $user->createToken('Personal Access Token')->plainTextToken;
        $creationTimestamp = time();
        $expirationTimestamp = time() + (4 * 60 * 60);
        $tokenData = [
            "created_at"=>$creationTimestamp,
            "expired_at" =>$expirationTimestamp,
            "user"=>$user
        ];
        Redis::set("user:session:$token", json_encode($tokenData));

        return response()->json([
            'user' => $user,
            'token' =>$token,
        ]);
    }



    public function logout(Request $request)
    {
        if(!Auth::check()){

            return response()->json([
                'message' => 'User is not even logged in'
            ], 401); 
        }
        
        $request->user()->tokens()->delete();

        $accessToken = explode("Bearer ", $request->header('Authorization'))[1];

        if(!Redis::exists("user:session:$accessToken"))
        {
            return response()->json([
                'message' => 'User Token not found',
                'data'=> Redis::get("user:session:$accessToken")
            ], 401);
        }

        Redis::del("user:session:$accessToken");

        return response()->json([
        'message' => 'Successfully logged out'
        ]);

    }

}
