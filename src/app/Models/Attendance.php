<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
    ];

    protected $dates = [
        'work_date',
        'clock_in',
        'clock_out',
    ];

    /* リレーション */
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    /* 日付フォーマット（06/01(木)） */
    public function getDateLabelAttribute()
    {
        return Carbon::parse($this->work_date)->format('m/d（D）');
    }

    /* 出勤時間フォーマット（H:i） */
    public function getStartTimeLabelAttribute()
    {
        return $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i') : '';
    }

    /* 退勤時間フォーマット（H:i） */
    public function getEndTimeLabelAttribute()
    {
        return $this->clock_out ? Carbon::parse($this->clock_out)->format('H:i') : '';
    }

    /* 休憩時間の合計（H:i） */
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

    /* 合計勤務時間（H:i） */
    public function getTotalTimeLabelAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return '';
        }

        $workSeconds = Carbon::parse($this->clock_out)->diffInSeconds(Carbon::parse($this->clock_in));

        // 休憩を引く
        $breakSeconds = 0;
        foreach ($this->breakTimes as $break) {
            if ($break->break_in && $break->break_out) {
                $breakSeconds += Carbon::parse($break->break_out)->diffInSeconds(Carbon::parse($break->break_in));
            }
        }

        $total = $workSeconds - $breakSeconds;

        return gmdate('H:i', $total);
    }
}
