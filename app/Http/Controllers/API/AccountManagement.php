<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\mUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountManagement extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('user-access');
            return response()->json(['message' => 'success', 'token' => $token->plainTextToken], 200);
        }
    }

    public function register(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = mUser::create([
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        if (!is_null($user)) {
            return response()->json(['message'=>'success','data'=>$user]);
        }
    }

    public function logout()
    {
        $user = Auth::user();
        if($user->tokens()->delete()){
            return response()->json(['message'=>'success'],200);
        }
        return response()->json(['message'=>'failed'],400);
    }

    public function edit()
    {

    }
}
