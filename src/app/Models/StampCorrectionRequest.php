<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'reason',
        'status',
    ];

    /**
     * 申請者（User）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 対象勤怠（Attendance）
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
