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
            $fname = Auth::user()->fName;

            return response()->json([
                'role' => $role,
                'fName' => $fname,
            ]);
        } else {
            // Authentication failed
            return response()->json(['login_error' => 'Invalid email or password']);
        }
    }

    // public function logout(Request $request)
    // {
    //     // Revoke the user's current API token
    //     $request->user()->currentAccessToken()->delete();

    //     // Return a success message
    //     return response()->json(['message' => 'User logged out successfully']);
    // }
}
