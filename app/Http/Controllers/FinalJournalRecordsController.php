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
    // get trainee list who have completed the training
    function getCompletedTraineeList($evaluator_id)
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

        return $traineeIdsWithSameDuration;
    }

    function getPendingApprovalRecords($evaluator_id)
    {
        $traineeIdsWithSameDuration[] = $this->getCompletedTraineeList($evaluator_id);

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

    // get all trainee records for a evaluator which are approved
    public function getAllTraineeRecordsForEvaluatorApproved($evaluator_id)
    {
        $completedTraineeList = final_journal_records::select('trainee_id')
            ->where('evaluator_id', $evaluator_id)
            ->get();

        $records = journal_records::select('trainee_id', 'description', 'solutions', 'week', 'month', 'year')
            ->whereIn('trainee_id', $completedTraineeList)
            ->where('approved', 1)
            ->get();

        $groupedData = $records->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->groupBy(['month'])
                    ->map(function ($weekRecords) {
                        return $weekRecords->values();
                    });
            });

        $reports = MonthJournalRecord::select('trainee_id', 'records', 'number_of_leave', 'month', 'year')
            ->whereIn('trainee_id', $completedTraineeList)
            ->get();

        $groupedReports = $reports->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->values();
            });

        $eval_reports = final_journal_records::select('trainee_id', 'record')
            ->where('evaluator_id', $evaluator_id)
            ->get();

        $groupedEvalReports = $eval_reports->groupBy('trainee_id')
            ->map(function ($traineeRecords) {
                return $traineeRecords->values();
            });

        $mergedData = [];

        foreach ($groupedReports as $trainee_id => $entries) {
            foreach ($entries as $entry) {
                [
                    $mergedData[$trainee_id]["evalReport"] = $groupedEvalReports[$trainee_id][0]["record"],
                    $mergedData[$trainee_id]["months"][$entry["month"]] = [
                        "reports" => $entry["records"],
                        "number_of_leave" => $entry["number_of_leave"],
                        "weekly" => $groupedData[$trainee_id][$entry["month"]],
                    ]
                ];
            }
        }

        return response()->json(['records' => $mergedData]);
    }
}