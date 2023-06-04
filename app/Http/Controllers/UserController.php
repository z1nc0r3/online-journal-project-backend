<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Connection;

class UserController extends Controller
{

    /* Handle creating new users */

    // Create a new trainee user
    public function createTrainee(Request $request)
    {
        $request->validate([
            'fName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

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

        return response()->json(['message' => 'Trainee user created successfully']);
    }

    // Create a new supervisor user
    public function createSupervisor(Request $request) {
        $request->validate([
            'fName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        User::create([
            'role' => 'supervisor',
            'fName' => $request->fName,
            'email' => $request->email,
            'phone' => $request->phone,
            'estName' => $request->estName,
            'estAddress' => $request->estAddress,
            'password' => $request->password,
        ]);

        return response()->json(['message' => 'Supervisor user created successfully']);
    }

    // Create a new evaluator user
    public function createEvaluator(Request $request) {
        $request->validate([
            'fName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        User::create([
            'role' => 'evaluator',
            'fName' => $request->fName,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
        ]);

        return response()->json(['message' => 'Evaluator user created successfully']);
    }

    /* Handle getting user details */

    // Get the Trainee details
    public function getUserDetails($traineeId)
    {
        $user = User::with(['connection.supervisor', 'connection.evaluator'])
            ->select('id', 'fName', 'department')
            ->where('id', $traineeId)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $response = [
            'trainee_id' => $user->id,
            'fName' => $user->fName,
            'department' => $user->department,
            'supervisor_id' => $user->connection->supervisor->id,
            'supervisor_name' => $user->connection->supervisor->fName,
            'evaluator_id' => $user->connection->evaluator->id,
            'evaluator_name' => $user->connection->evaluator->fName,
        ];

        return response()->json(['user' => $response]);
    }

}
