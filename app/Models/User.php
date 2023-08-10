<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'fName',
        'regNo',
        'department',
        'address',
        'email',
        'phone',
        'estName',
        'estAddress',
        'startDate',
        'duration',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function traineeConnection()
    {
        return $this->hasMany(Connection::class, 'trainee_id');
    }

    public function supervisorConnection()
    {
        return $this->hasMany(Connection::class, 'supervisor_id');
    }

    public function evaluatorConnection()
    {
        return $this->hasMany(Connection::class, 'evaluator_id');
    }
}
