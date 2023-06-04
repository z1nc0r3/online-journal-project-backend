<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'supervisor_id',
        'supervisor_name',
        'evaluator_id',
        'evaluator_name',
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
