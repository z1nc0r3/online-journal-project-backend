<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MonthJournalRecord;
use App\Models\journal_records;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MonthJournalRecordController extends Controller
{

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
        foreach ($request->all() as $record) {
            $validator = Validator::make($record, [
                'trainee_id' => 'required',
                'supervisor_id' => 'required',
                'evaluator_id' => 'required',
                'record' => 'required',
                'leaves' => 'required',
                'month' => 'required',
                'year' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Validation failed'], 422);
            }

            $this->makeApproved($record['trainee_id'], $record['month']);

            MonthJournalRecord::create([
                'trainee_id' => $record['trainee_id'],
                'supervisor_id' => $record['supervisor_id'],
                'evaluator_id' => $record['evaluator_id'],
                'records' => $record['record'],
                'number_of_leave' => $record['leaves'],
                'month' => $record['month'],
                'year' => $record['year']
            ]);
        }

        return response()->json(['message' => 'Review added successfully']);
    }
}
