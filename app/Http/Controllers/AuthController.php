<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);


        $user = User::where('email', $request->input('email'))->first();


        if (Hash::check($request->input('password'), $user->password)) {
            $apikey = base64_encode(Str::random(40));


            $user->update([
                'api_key' => $apikey
            ]);


            return response()->json([
                'success' => true,
                'api_key' => $apikey
            ]);
        } else {
            return response()->json([
                'success' => false
            ], 401);
        }
    }


    public function logout(Request $request)
    {
        if ($request->header('api_key')) {
            $user = User::where('api_key', $request->input('api_key'));
            $user->update([
                'api_key' => NULL
            ]);


            return response()->json([
                'success' => true,
                'message' => "Berhasil Logout!"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
            ], 400);
        }
    }
}