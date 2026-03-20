<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 勤怠モデル
 *
 * - 出勤・退勤時刻
 * - 休憩時間（複数）
 * - 勤務時間計算
 * - 日付・時間のフォーマット済みアクセサ
 *
 * @property int $id
 * @property int $user_id ユーザーID
 * @property string $work_date 勤務日
 * @property string|null $clock_in 出勤時刻
 * @property string|null $clock_out 退勤時刻
 * @property string|null $status 勤務ステータス
 * @property string|null $note 備考
 * @property string|null $break_time 休憩時間（旧仕様）
 * @property string|null $working_time 勤務時間（旧仕様）
 *
 * @property-read string $date_label 日付（例: 06/01(木)）
 * @property-read string $start_time_label 出勤時刻（H:i）
 * @property-read string $end_time_label 退勤時刻（H:i）
 * @property-read string $break_time_label 休憩合計（H:i）
 * @property-read string $total_time_label 勤務合計（休憩差し引き後）（H:i）
 * @property-read string $break_times_label 休憩一覧（例: "12:00 - 12:30, 15:00 - 15:15"）
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BreakTime[] $breakTimes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\StampCorrectionRequest[] $correctionRequests
 * @property-read \App\Models\User $user
 */

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

    /**
     * 休憩時間（複数）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    /**
     * 日付フォーマット（06/01(木)）
     *
     * @return string
     */
    public function getDateLabelAttribute()
    {
        return Carbon::parse($this->work_date)->format('m/d（D）');
    }

    /**
     * 出勤時間フォーマット（H:i）
     *
     * @return string
     */
    public function getStartTimeLabelAttribute()
    {
        return $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i') : '';
    }

    /**
     * 退勤時間フォーマット（H:i）
     *
     * @return string
     */
    public function getEndTimeLabelAttribute()
    {
        return $this->clock_out ? Carbon::parse($this->clock_out)->format('H:i') : '';
    }

    /**
     * 休憩時間の合計（H:i）
     *
     * @return string
     */
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

    /**
     * 合計勤務時間（休憩差し引き後）（H:i）
     *
     * @return string
     */
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

    /**
     * 打刻修正申請（StampCorrectionRequest）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function correctionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    /**
     * 休憩時間一覧（例: "12:00 - 12:30, 15:00 - 15:15"）
     *
     * @return string
     */
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

    /**
     * ユーザー（User）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
