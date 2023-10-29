<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\final_journal_records;
use App\Models\MonthJournalRecord;
use App\Models\journal_records;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FinalJournalRecordsController extends Controller
{
    function getPendingApprovalRecords($evaluator_id)
    {
        // get list of trainees who have completed their internship
        $monthlyRecords = MonthJournalRecord::select('trainee_id')
            ->where('evaluator_id', $evaluator_id)
            ->where('approved', 0)
            ->get();

        $groupedData = $monthlyRecords->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->count();
            });

        $traineeIds = $monthlyRecords->pluck('trainee_id')->toArray();

        $traineeDurations = User::select('id', 'duration')
            ->whereIn('id', $traineeIds)
            ->get();

        $groupedDurations = $traineeDurations->groupBy('id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->pluck('duration')->first();
            });

        $traineeIdsWithSameDuration = [];

        foreach ($groupedData as $traineeId => $count) {
            if ($count == $groupedDurations[$traineeId]) {
                $traineeIdsWithSameDuration[] = $traineeId;
            }
        }

        // get all records for above trainees
        $records = journal_records::select('trainee_id', 'description', 'solutions', 'week', 'month', 'year')
            ->whereIn('trainee_id', $traineeIdsWithSameDuration)
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
            ->whereIn('trainee_id', $traineeIdsWithSameDuration)
            ->get();

        $groupedReports = $reports->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->values();
            });

        $mergedData = $groupedData;

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


    // Get data which had the approval page
    function getApprovedData($evaluator_id)
    {
        // Get evaluator id from request
        $records = Connection::where('evaluator_id', $evaluator_id)->get();
        // Need loop for run through all records
        foreach ($records as $record) {
            $trainee_id = $record->trainee_id;

            // check every trainee id available in final journal records table
            $final_records = final_journal_records::select()->where('trainee_id', $trainee_id)->first();

            // if available get the final journal records table data by trainee id
            if ($final_records != null) {
                $month_records = MonthJournalRecord::select()->where('trainee_id', $trainee_id)->get();

                // array
                $response = [];

                // for each month records get the month journal records table data by trainee id
                foreach ($month_records as $month_record) {

                    $month = $month_record->month;
                    $year = $month_record->year;

                    $records = journal_records::select()->where('trainee_id', $trainee_id)->where('month', $month)->where('year', $year)->where('approved', '1')->get();

                    // Create an associative array to represent this month's data.
                    $month_data = [
                        'month_record' => $month_record, // You can adjust this to include only the details you want.
                        'weeks' => $records
                    ];

                    // Append this month's data to the response array.
                    $response[] = $month_data;
                }

                return response()->json(['data' => $response]);
            }
        }
    }

    // Set approval status
    function setApproval(Request $request, $evaluator_id)
    {
        // Get trainee id from request
        $trainee_id = $request->trainee_id;

        // Get month and year from request
        $month = $request->month;
        $year = $request->year;

        // Get approval status from request
        $approval_status = $request->approval_status;

        // Get final journal records table data by trainee id
        $final_records = final_journal_records::select()->where('trainee_id', $trainee_id)->first();

        // if not available create new record
        if ($final_records == null) {
            final_journal_records::create([
                'trainee_id' => $trainee_id,
                'evaluator_id' => $evaluator_id,
                'month' => $month,
                'year' => $year,
                'approved' => $approval_status,
            ]);
        } else {
            // if available update the record
            final_journal_records::where('trainee_id', $trainee_id)->update([
                'evaluator_id' => $evaluator_id,
                'month' => $month,
                'year' => $year,
                'approved' => $approval_status,
            ]);
        }

        return response()->json(['message' => 'Approval status updated successfully']);
    }

    // Add evaluator review
    function addEvaluatorReview(Request $request)
    {
        $trainee_id = $request->trainee_id;
        $supervisor_id = $request->supervisor_id;
        $evaluator_id = $request->evaluator_id;
        $review = $request->record;

        $request->validate([
            'record' => 'required',
        ]);

        // Get final journal records table data by trainee id
        $final_records = final_journal_records::select()->where('trainee_id', $trainee_id)->first();

        // if not available create new record
        if ($final_records == null) {
            final_journal_records::create([
                'trainee_id' => $trainee_id,
                'supervisor_id' => $supervisor_id,
                'evaluator_id' => $evaluator_id,
                'record' => $review,
            ]);

            $this->makeApproved($trainee_id);
            
        } else {
            // if available update the record
            final_journal_records::where('trainee_id', $trainee_id)->update([
                'record' => $review,
            ]);
        }

        return response()->json(['message' => 'Evaluator review added successfully']);
    }

    // Make approved in the monthly journal record table
    public function makeApproved($traineeId)
    {
        $records = MonthJournalRecord::where('trainee_id', $traineeId)
            ->where('approved', 0)
            ->update([
                'approved' => 1
            ]);

        return response()->json(['message' => 'Records set as approved.']);
    }
}
