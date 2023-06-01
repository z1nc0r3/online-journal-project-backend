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
        User::create([
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

    public function createSupervisor(Request $request) {
        // Validate the request data
        $request->validate([
            'fName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        // Create a new supervisor user
        User::create([
            'role' => 'supervisor',
            'fName' => $request->fName,
            'email' => $request->email,
            'phone' => $request->phone,
            'estName' => $request->estName,
            'estAddress' => $request->estAddress,
            'password' => $request->password,
        ]);

        // Return a success message or any other response as needed
        return response()->json(['message' => 'Supervisor user created successfully']);
    }

    public function createEvaluator(Request $request) {
        // Validate the request data
        $request->validate([
            'fName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        // Create a new evaluator user
        User::create([
            'role' => 'evaluator',
            'fName' => $request->fName,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
        ]);

        // Return a success message or any other response as needed
        return response()->json(['message' => 'Evaluator user created successfully']);
    }

}
