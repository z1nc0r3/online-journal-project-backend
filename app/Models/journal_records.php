<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class journal_records extends Model
{
    use HasFactory;

    protected $fillable = [
        'discription',
        'prob_and_sol',
        'week',
        'month',
        'year',
    ];
}
