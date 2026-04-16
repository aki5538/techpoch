<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'note',
        'break_time',
        'working_time',
    ];

    protected $dates = [
        'clock_in',
        'clock_out',
    ];

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function getDateLabelAttribute()
    {
        $wMap = [
            'Sun' => '日',
            'Mon' => '月',
            'Tue' => '火',
            'Wed' => '水',
            'Thu' => '木',
            'Fri' => '金',
            'Sat' => '土',
        ];

        $date = Carbon::parse($this->work_date);
        $w = $wMap[$date->format('D')];

        return $date->format('m/d') . '（' . $w . '）';
    }

    public function getStartTimeLabelAttribute()
    {
        return $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i') : '';
    }

    public function getEndTimeLabelAttribute()
    {
        return $this->clock_out ? Carbon::parse($this->clock_out)->format('H:i') : '';
    }

    public function getBreakTimeLabelAttribute()
    {
        $totalSeconds = 0;

        foreach ($this->breakTimes as $break) {
            if ($break->break_in && $break->break_out) {
                $totalSeconds += Carbon::parse($break->break_out)->diffInSeconds(Carbon::parse($break->break_in));
            }
        }

        return gmdate('H:i', $totalSeconds);
    }

    public function getTotalTimeLabelAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return '';
        }

        $workSeconds = Carbon::parse($this->clock_out)->diffInSeconds(Carbon::parse($this->clock_in));

        $breakSeconds = 0;
        foreach ($this->breakTimes as $break) {
            if ($break->break_in && $break->break_out) {
                $breakSeconds += Carbon::parse($break->break_out)->diffInSeconds(Carbon::parse($break->break_in));
            }
        }

        $total = $workSeconds - $breakSeconds;

        return gmdate('H:i', $total);
    }

    public function getBreakTimesLabelAttribute()
    {
        $labels = [];

        foreach ($this->breakTimes as $break) {
            $in  = $break->break_in  ? Carbon::parse($break->break_in)->format('H:i') : '';
            $out = $break->break_out ? Carbon::parse($break->break_out)->format('H:i') : '';

            if ($in && $out) {
                $labels[] = $in . ' - ' . $out;
            }
        }

        return implode(', ', $labels);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }
}
