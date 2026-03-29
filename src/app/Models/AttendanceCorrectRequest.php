<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    protected $table = 'attendance_correct_requests';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'clock_in',
        'clock_out',
        'break_in',
        'break_out',
        'reason',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function correctBreakTimes()
    {
        return $this->hasMany(CorrectBreakTime::class);
    }
}