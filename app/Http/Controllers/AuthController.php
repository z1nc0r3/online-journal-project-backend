<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // Attempt to authenticate the user
        if (Auth::attempt($credentials)) {
            $role = Auth::user()->role;
            $user_id = Auth::user()->id;
            $fname = Auth::user()->fName;

            return response()->json([
                'role' => $role,
                'user_id' => $user_id,
                'fName' => $fname,
            ]);
        } else {
            // Authentication failed
            return response()->json(['login_error' => 'Invalid email or password']);
        }
    }
}
