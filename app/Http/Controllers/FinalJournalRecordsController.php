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

    // Get data to pending approval page
    function getPendingApprovalRecords($evaluator_id)
    {
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

        /* $reports = final_journal_records::select('id', 'trainee_id', 'records', 'number_of_leave', 'month', 'year')
            ->where('evaluator_id', $evaluator_id)
            ->get();

        $groupedReports = $reports->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->values();
            }); */

        return response()->json(['records' => $groupedDurations]);
    }

    // get all trainee records + supervisor reviews using trainee_id
    public function getAllCompletedTraineeRecordsPending($supervisor_id)
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

    // Get data to pending approval page
    function getPendingApprovalData($evaluator_id)
    {
        // Get evaluator id from request
        $records = Connection::where('evaluator_id', $evaluator_id)->get();
        // Need loop for run through all records
        foreach ($records as $record) {
            $trainee_id = $record->trainee_id;

            // check every trainee id available in final journal records table
            $final_records = final_journal_records::select()->where('trainee_id', $trainee_id)->first();

            // if not available get the trainee duration from the user table by trainee id
            if ($final_records == null) {
                $trainee_duration = User::select('duration')->where('id', $trainee_id)->first();
                $trainee_duration = $trainee_duration->duration;

                // get the how many records available in month journal records table by same trainee id
                $month_records = MonthJournalRecord::select()->where('trainee_id', $trainee_id)->get();
                $month_records_count = count($month_records);

                // check trainee duration and month records count equal or not
                if ($trainee_duration == $month_records_count) {

                    // if equal get the month journal records table data by trainee id
                    $month_records = MonthJournalRecord::select()->where('trainee_id', $trainee_id)->get();

                    // array
                    $response = [];

                    // for each month records get the month journal records table data by trainee id
                    foreach ($month_records as $month_record) {

                        $month = $month_record->month;
                        $year = $month_record->year;

                        $records = journal_records::select()->where('trainee_id', $trainee_id)->where('month', $month)->where('year', $year)->where('approved', '0')->get();

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
}
