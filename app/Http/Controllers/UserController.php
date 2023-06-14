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
    public function createSupervisor(Request $request)
    {
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
    public function createEvaluator(Request $request)
    {
        $request->validate([
            'fName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        User::create([
            'role' => 'evaluator',
            'fName' => $request->fName,
            'department' => $request->department,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
        ]);

        return response()->json(['message' => 'Evaluator user created successfully']);
    }

    /* Handle getting user details */

    // Get trainee list
    public function getTraineeList()
    {
        $trainees = User::with(['traineeConnection'])
            ->select('id', 'fName', 'department')
            ->where('role', 'trainee')
            ->get();

        return response()->json(['trainees' => $trainees]);
    }

    // Get supervisor list
    public function getSupervisorList()
    {
        $supervisors = User::with(['supervisorConnection'])
            ->select('id', 'fName', 'estName')
            ->where('role', 'supervisor')
            ->get();

        return response()->json(['supervisors' => $supervisors]);
    }

    // Get evaluator list
    public function getEvaluatorList()
    {
        $evaluators = User::with(['evaluatorConnection'])
            ->select('id', 'fName')
            ->where('role', 'evaluator')
            ->get();

        return response()->json(['evaluators' => $evaluators]);
    }

    // Get the Trainee details
    public function getTraineeDetails($traineeId)
    {
        $user = User::with(['supervisorConnection.supervisor', 'evaluatorConnection.evaluator'])
            ->select()
            ->where('id', $traineeId)
            ->where('role', 'trainee')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        try {
            $supervisor_id = $user->supervisorConnection->supervisor->id;
            $supervisor_name = $user->supervisorConnection->supervisor->fName;
        } catch (\Throwable $th) {
            $supervisor_id = null;
            $supervisor_name = null;
        }

        try {
            $evaluator_id = $user->evaluatorConnection->evaluator->id;
            $evaluator_name = $user->evaluatorConnection->evaluator->fName;
        } catch (\Throwable $th) {
            $evaluator_id = null;
            $evaluator_name = null;
        }

        $response = [
            'trainee_id' => $user->id,
            'fName' => $user->fName,
            'regNo' => $user->regno,
            'department' => $user->department,
            'address' => $user->address,
            'email' => $user->email,
            'phone' => $user->phone,
            'estName' => $user->estName,
            'estAddress' => $user->estAddress,
            'startDate' => $user->startDate,
            'duration' => $user->duration,
            'supervisor_id' => $supervisor_id,
            'supervisor_name' => $supervisor_name,
            'evaluator_id' => $evaluator_id,
            'evaluator_name' => $evaluator_name,
        ];

        return response()->json(['user' => $response]);
    }

    // Get the Supervisor details
    public function getSupervisorDetails($supervisorId)
    {
        $user = User::select()
            ->where('id', $supervisorId)
            ->where('role', 'supervisor')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $response = [
            'supervisor_id' => $user->id,
            'fName' => $user->fName,
            'email' => $user->email,
            'phone' => $user->phone,
            'estName' => $user->estName,
            'estAddress' => $user->estAddress,
            'trainees' => $user->traineeConnection,
        ];

        return response()->json(['user' => $response]);
    }

    // Get the Evaluator details
    public function getEvaluatorDetails($evaluatorId)
    {
        $user = User::select()
            ->where('id', $evaluatorId)
            ->where('role', 'evaluator')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $response = [
            'evaluator_id' => $user->id,
            'fName' => $user->fName,
            'department' => $user->department,
            'email' => $user->email,
            'phone' => $user->phone,
            'trainees' => $user->traineeConnection,
        ];

        return response()->json(['user' => $response]);
    }


    /* Handle updating user details */

    // Update trainee details
    public function updateTrainee(Request $request, $traineeId)
    {
        $user = User::where('id', $traineeId)
            ->where('role', 'trainee')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'fName' => 'required',
            'regNo' => 'required',
            'department' => 'required',
            'address' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'estName' => 'required',
            'estAddress' => 'required',
            'startDate' => 'required',
            'duration' => 'required',
        ]);

        $user->update([
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
        ]);

        return response()->json(['message' => 'Trainee user updated successfully']);
    }

    // Update supervisor details
    public function updateSupervisor(Request $request, $supervisorId)
    {
        $user = User::where('id', $supervisorId)
            ->where('role', 'supervisor')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'fName' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'estName' => 'required',
            'estAddress' => 'required',
        ]);

        $user->update([
            'fName' => $request->fName,
            'email' => $request->email,
            'phone' => $request->phone,
            'estName' => $request->estName,
            'estAddress' => $request->estAddress,
        ]);

        return response()->json(['message' => 'Supervisor user updated successfully']);
    }
}
