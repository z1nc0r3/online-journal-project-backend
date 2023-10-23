<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthJournalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'supervisor_id',
        'evaluator_id',
        'records',
        'number_of_leave',
        'month',
        'year',
    ];

    public function trainee()
    {
        return $this->belongsTo(User::class, 'trainee_id');
    }
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
