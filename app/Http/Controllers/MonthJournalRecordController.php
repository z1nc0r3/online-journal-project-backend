<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MonthJournalRecord;
use App\Models\journal_records;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MonthJournalRecordController extends Controller
{
    // get all trainee records for a supervisor which are approved
    public function getAllTraineeRecordsForSupervisorApproved($supervisor_id)
    {
        $records = journal_records::select('trainee_id', 'evaluator_id', 'description', 'solutions', 'week', 'month', 'year')
            ->where('supervisor_id', $supervisor_id)
            ->where('approved', 1)
            ->get();

        $groupedData = $records->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->groupBy(['month'])
                    ->map(function ($weekRecords) {
                        return $weekRecords->values();
                    });
            });

        $reports = MonthJournalRecord::select('id', 'trainee_id', 'records', 'number_of_leave', 'month', 'year')
            ->where('supervisor_id', $supervisor_id)
            ->get();

        $groupedReports = $reports->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->values();
            });

        $mergedData = $groupedData; // Start with a copy of A

        foreach ($groupedReports as $trainee_id => $entries) {
            foreach ($entries as $entry) {
                $mergedData[$trainee_id][$entry["month"]] = [
                    "id" => $entry["id"],
                    "reports" => $entry["records"],
                    "number_of_leave" => $entry["number_of_leave"],
                    "records" => $groupedData[$trainee_id][$entry["month"]]
                ];
            }
        }

        return response()->json(['records' => $mergedData]);
    }

    // set records as approved
    public function makeApproved($traineeId, $month)
    {
        $records = journal_records::where('trainee_id', $traineeId)
            ->where('month', $month)
            ->where('approved', 0)
            ->update([
                'approved' => 1
            ]);

        return response()->json(['message' => 'Records set as approved.']);
    }

    // Set supervisor review
    public function addSupervisorReview(Request $request)
    {
        $request->validate([
            'trainee_id' => 'required',
            'supervisor_id' => 'required',
            'evaluator_id' => 'required',
            'record' => 'required',
            'leaves' => 'required',
            'month' => 'required',
            'year' => 'required'
        ]);

        $this->makeApproved($request['trainee_id'], $request['month']);

        MonthJournalRecord::create([
            'trainee_id' => $request['trainee_id'],
            'supervisor_id' => $request['supervisor_id'],
            'evaluator_id' => $request['evaluator_id'],
            'records' => $request['record'],
            'number_of_leave' => $request['leaves'],
            'month' => $request['month'],
            'year' => $request['year']
        ]);

        return response()->json(['message' => 'Review added successfully']);
    }

    // Update supervisor review
    public function updateSupervisorReview(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'record' => 'required',
            'leaves' => 'required'
        ]);

        MonthJournalRecord::where('id', $request['id'])
            ->update([
                'records' => $request['record'],
                'number_of_leave' => $request['leaves']
            ]);

        return response()->json(['message' => 'Review updated successfully']);
    }
}
