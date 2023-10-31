<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Connection;
use Illuminate\Support\Facades\Hash;

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

        try {
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating user']);
        }

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

        if (!$trainees) {
            return response()->json(['error' => 'No trainees found'], 404);
        }

        return response()->json(['trainees' => $trainees]);
    }

    // Get Trainee list with supervisor id
    public function getTraineeListWithSupervisorId($supervisorId)
    {
        $trainees = User::with(['traineeConnection'])
            ->select('id', 'fName', 'department', 'duration')
            ->where('role', 'trainee')
            ->whereHas('traineeConnection', function ($query) use ($supervisorId) {
                $query->where('supervisor_id', $supervisorId);
            })
            ->get();

        if (!$trainees) {
            return response()->json(['error' => 'No trainees found'], 404);
        }

        return response()->json(['trainees' => $trainees]);
    }

    // Get Trainee list with evaluator id
    public function getTraineeListWithEvaluatorId($evaluatorId)
    {
        $trainees = User::with(['traineeConnection'])
            ->select('id', 'fName', 'department', 'duration')
            ->where('role', 'trainee')
            ->whereHas('traineeConnection', function ($query) use ($evaluatorId) {
                $query->where('evaluator_id', $evaluatorId);
            })
            ->get();

        if (!$trainees) {
            return response()->json(['error' => 'No trainees found'], 404);
        }

        return response()->json(['trainees' => $trainees]);
    }

    // Get supervisor list
    public function getSupervisorList()
    {
        $supervisors = User::with(['supervisorConnection'])
            ->select('id', 'fName', 'estName')
            ->where('role', 'supervisor')
            ->get();

        if (!$supervisors) {
            return response()->json(['error' => 'No supervisors found'], 404);
        }

        return response()->json(['supervisors' => $supervisors]);
    }

    // Get evaluator list
    public function getEvaluatorList()
    {
        $evaluators = User::with(['evaluatorConnection'])
            ->select('id', 'fName', 'department')
            ->where('role', 'evaluator')
            ->get();

        if (!$evaluators) {
            return response()->json(['error' => 'No evaluators found'], 404);
        }

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

    // Update evaluator details
    public function updateEvaluator(Request $request, $evaluatorId)
    {
        $user = User::where('id', $evaluatorId)
            ->where('role', 'evaluator')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'fName' => 'required',
            'department' => 'required',
            'email' => 'required|email',
            'phone' => 'required'
        ]);

        $user->update([
            'fName' => $request->fName,
            'department' => $request->department,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return response()->json(['message' => 'Evaluator user updated successfully']);
    }

    // Assign a supervisor and evaluator to a trainee
    public function assignSupervisorAndEvaluator(Request $request)
    {
        $traineeId = $request->id;
        $traineeName = $request->fName;
        $supervisorId = $request->input('trainee_connection.supervisor_id');
        $supervisorName = $request->input('trainee_connection.supervisor_name');
        $evaluatorId = $request->input('trainee_connection.evaluator_id');
        $evaluatorName = $request->input('trainee_connection.evaluator_name');

        // delete the connection if both supervisor and evaluator are not assigned
        if (!$supervisorId && !$evaluatorId) {
            Connection::where('trainee_id', $traineeId)
                ->delete();
            return response()->json(['message' => 'Connection deleted successfully']);
        } else if (!$supervisorId || !$evaluatorId) {
            return response()->json(['message' => 'Please assign both Supervisor and Evaluator'], 400);
        }

        $user = Connection::where('trainee_id', $traineeId)
            ->first();

        if (!$user) {
            Connection::create([
                'trainee_id' => $traineeId,
                'trainee_name' => $traineeName,
                'supervisor_id' => $supervisorId,
                'supervisor_name' => $supervisorName,
                'evaluator_id' => $evaluatorId,
                'evaluator_name' => $evaluatorName,
            ]);
        } else {
            $user->update([
                'supervisor_id' => $supervisorId,
                'supervisor_name' => $supervisorName,
                'evaluator_id' => $evaluatorId,
                'evaluator_name' => $evaluatorName,
            ]);
        }

        return response()->json(['message' => 'Supervisor and Evaluator assigned successfully']);
    }

    // Create bulk users using json input
    public function createBulkUsers(Request $request)
    {
        $users = $request->users;

        foreach ($users as $user) {
            if ($user['role'] == 'trainee') {
                User::create([
                    'role' => $user['role'],
                    'fName' => $user['fName'],
                    'regNo' => $user['regNo'],
                    'department' => $user['department'],
                    'address' => $user['address'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'estName' => $user['estName'],
                    'estAddress' => $user['estAddress'],
                    'startDate' => $user['startDate'],
                    'duration' => $user['duration'],
                    'password' => $user['password'],
                ]);
            } else if ($user['role'] == 'supervisor') {
                User::create([
                    'role' => $user['role'],
                    'fName' => $user['fName'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'estName' => $user['estName'],
                    'estAddress' => $user['estAddress'],
                    'password' => $user['password'],
                ]);
            } else if ($user['role'] == 'evaluator') {
                User::create([
                    'role' => $user['role'],
                    'fName' => $user['fName'],
                    'department' => $user['department'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'password' => $user['password'],
                ]);
            }
        }

        if (!$users) {
            return response()->json(['message' => 'Error creating users']);
        }

        return response()->json(['message' => 'Users created successfully']);
    }

    // Reset user password
    public function resetPassword($userId)
    {
        $user = User::where('id', $userId)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update([
            'password' => "!Int3rn@1ee7"
        ]);

        return response()->json(['message' => 'Password reset successfully']);
    }

    // Delete user
    public function deleteUser($userId)
    {
        $user = User::where('id', $userId)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
