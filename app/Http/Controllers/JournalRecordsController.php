<?php

namespace App\Http\Controllers;

use App\Models\journal_records;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class JournalRecordsController extends Controller
{
    public function getRecords()
    {
        $records = journal_records::select('id', 'discription', 'week')
                        ->get();

        return response()->json(['records' => $records]);
    }

    public function createRecord(Request $request)
    {

        journal_records::create([
            'discription' => $request->discription,
            'prob_and_sol' => $request->prob_and_sol,
            'week' => $request->week,
            'month' => $request->month,
            'year' => $request->year,
        ]);

        return response()->json(['message' => 'Record added successfully']);
    }
}
