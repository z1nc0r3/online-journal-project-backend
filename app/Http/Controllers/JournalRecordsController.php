<?php

namespace App\Http\Controllers;

use App\Models\journal_records;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class JournalRecordsController extends Controller
{
    // get all records for a trainee
    public function getAllTraineeRecords($trainee_id)
    {
        $records = journal_records::select('id', 'description', 'solutions', 'week', 'month', 'year')
            ->where('trainee_id', $trainee_id)
            ->get();

        return response()->json(['records' => $records]);
    }

    // get current month records in a month for a trainee
    public function getCurrentMonthRecords(Request $request, $trainee_id)
    {
        $month = $request->query('month');
        $year = $request->query('year');

        $records = journal_records::select('id', 'description', 'solutions', 'week')
            ->where('trainee_id', $trainee_id)
            ->where('month', $month) // Assuming you want to filter by creation date
            ->where('year', $year)
            ->get();

        return response()->json(['records' => $records]);
    }

    // get all trainee records for a supervisor
    public function getAllTraineeRecordsForSupervisor($supervisor_id)
    {
        $records = journal_records::select('id', 'trainee_id', 'evaluator_id', 'description', 'solutions', 'week', 'month', 'year')
            ->where('supervisor_id', $supervisor_id)
            ->get();

        return response()->json(['records' => $records]);
    }

    // get all trainee records for a supervisor which are not approved
    public function getAllTraineeRecordsForSupervisorNotApproved($supervisor_id)
    {
        $records = journal_records::select('id', 'trainee_id', 'evaluator_id', 'description', 'solutions', 'week', 'month', 'year')
            ->where('supervisor_id', $supervisor_id)
            ->where('approved', 0)
            ->get();

        $groupedData = $records->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->groupBy(['month', 'week'])
                    ->map(function ($weekRecords) {
                        return $weekRecords->values();
                    });
            });

        return response()->json(['records' => $groupedData]);
    }

    // get all trainee records for a supervisor which are approved
    public function getAllTraineeRecordsForSupervisorApproved($supervisor_id)
    {
        $records = journal_records::select('id', 'trainee_id', 'evaluator_id', 'description', 'solutions', 'week', 'month', 'year')
            ->where('supervisor_id', $supervisor_id)
            ->where('approved', 1)
            ->get();

        $groupedData = $records->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->groupBy(['month', 'week'])
                    ->map(function ($weekRecords) {
                        return $weekRecords->values();
                    });
            });

        return response()->json(['records' => $groupedData]);
    }

    // create new record
    public function createRecord(Request $request)
    {
        journal_records::create([
            'trainee_id' => $request->user_id,
            'supervisor_id' => $request->supervisor_id,
            'evaluator_id' => $request->evaluator_id,
            'description' => $request->description,
            'solutions' => $request->solutions,
            'week' => $request->week,
            'month' => $request->month,
            'year' => $request->year,
        ]);

        return response()->json(['message' => 'Record added successfully']);
    }

    // get current month records in a month for a trainee
    public function getCurrentWeekRecord(Request $request, $trainee_id)
    {
        $week = $request->query('week');
        $month = $request->query('month');
        $year = $request->query('year');

        $records = journal_records::select('id', 'description', 'solutions', 'week')
            ->where('trainee_id', $trainee_id)
            ->where('week', $week)
            ->where('month', $month) // Assuming you want to filter by creation date
            ->where('year', $year)
            ->first();

        return response()->json(['record' => $records]);
    }

    // Update week record
    public function updateWeekRecord(Request $request, $trainee_id)
    {
        $week = $request->week;
        $month = $request->month;
        $year = $request->year;
        $record = journal_records::where('trainee_id', $trainee_id)
            ->where('week', $week)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $record->update([
            'description' => $request->description,
            'solutions' => $request->solutions,
        ]);
        return response()->json(['message' => 'Current Week Record Updated Successfully']);
    }
}
