<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\mUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountManagement extends Controller
{

    public function user()
    {
        $user = Auth::user();
        return response()->json($user, 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('user-access');
            return response()->json(['message' => 'success', 'token' => $token->plainTextToken, 'type_account'=>Auth::user()->role, 'user'=>$user], 200);
        }
        return response()->json(['message' => 'failed', 'token' => null], 200);
    }

    public function register(Request $request)
    {
        $credentials = $request->validate([
            'nama' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'alamat' => 'required',
            'nohp' => 'required',
        ]);

        $user = mUser::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'nohp' => $request->nohp,
        ]);

        if (!is_null($user)) {
            return response()->json(['message' => 'success', 'data' => $user], 200);
        }
        return response()->json(['message' => 'failed', 'data' => ''], 400);
    }

    public function logout()
    {
        $user = Auth::user();
        if ($user->tokens()->delete()) {
            return response()->json(['message' => 'success'], 200);
        }
        return response()->json(['message' => 'failed'], 400);
    }

    public function edit()
    {

    }
}
