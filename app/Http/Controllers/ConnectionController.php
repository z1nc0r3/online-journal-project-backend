<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Connection;

class ConnectionController extends Controller
{
    public function getDetailsFromTraineeID($trainee_id)
    {
        $records = Connection::select()
                        ->where('trainee_id', $trainee_id)
                        ->first();

        return response()->json(['records' => $records]);
    }

    public function getDetailsFromSupervisorID(Request $request, $supervisor_id)
    {
        $trainee_id = $request->query('trainee_id');

        $records = Connection::select()
                        ->where('supervisor_id', $supervisor_id)
                        ->where('trainee_id', $trainee_id)
                        ->first();

        return response()->json(['records' => $records]);
    }
}
