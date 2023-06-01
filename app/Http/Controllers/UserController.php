<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function createTrainee(Request $request)
    {
        // Validate the request data
        $request->validate([
            'fName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        // Create a new trainee user
        $user = User::create([
            'role' => 'trainee',
            'fName' => $request->fName,
            'regNo' => $request->regNo,
            'department' => $request->department,
            'address' => $request->address,
            'email' => $request->email,
            'phone' => $request->phone,
            'estName' => $request->estName,
            'estAddress' => $request->estAddress,
            'startDate' => $request->startDate,
            'duration' => $request->duration,
            'password' => $request->password,
        ]);

        // Return a success message or any other response as needed
        return response()->json(['message' => 'Trainee user created successfully']);
    }

    // Define similar methods for supervisor and evaluator user types

    

}
