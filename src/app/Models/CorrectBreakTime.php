<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectBreakTime extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'attendance_correct_request_id',
        'break_in',
        'break_out',
    ];

    public function request()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class);
    }
}
