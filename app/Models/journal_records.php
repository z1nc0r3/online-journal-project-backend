<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class journal_records extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'supervisor_id',
        'evaluator_id',
        'description',
        'solutions',
        'week',
        'month',
        'year',
    ];

    public function trainee()
    {
        return $this->belongsTo(User::class, 'trainee_id');
    }

}
