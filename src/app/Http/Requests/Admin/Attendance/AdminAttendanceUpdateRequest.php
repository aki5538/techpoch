<?php

namespace App\Http\Requests\Admin\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in'  => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],

            // 休憩（複数対応）
            'break_start_1' => ['nullable', 'date_format:H:i'],
            'break_end_1'   => ['nullable', 'date_format:H:i'],
            'break_start_2' => ['nullable', 'date_format:H:i'],
            'break_end_2'   => ['nullable', 'date_format:H:i'],

            // 備考
            'note' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            // 出勤・退勤
            'clock_in.required'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',

            // 休憩
            'break_start_1.date_format' => '休憩時間が不適切な値です',
            'break_end_1.date_format'   => '休憩時間もしくは退勤時間が不適切な値です',
            'break_start_2.date_format' => '休憩時間が不適切な値です',
            'break_end_2.date_format'   => '休憩時間もしくは退勤時間が不適切な値です',

            // 備考
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn  = $this->clock_in;
            $clockOut = $this->clock_out;

            // ① 出勤 > 退勤（仕様書 FN039-1）
            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // ② 休憩開始 < 出勤 or > 退勤（仕様書 FN039-2）
            $start1 = $this->break_start_1;
            $start2 = $this->break_start_2;

            if ($start1 && ($start1 < $clockIn || $start1 > $clockOut)) {
                $validator->errors()->add('break_start_1', '休憩時間が不適切な値です');
            }

            if ($start2 && ($start2 < $clockIn || $start2 > $clockOut)) {
                $validator->errors()->add('break_start_2', '休憩時間が不適切な値です');
            }



            // ③ 休憩終了 > 退勤（仕様書 FN039-3）
            $end1 = $this->break_end_1;
            $end2 = $this->break_end_2;

            if ($end1 && $end1 > $clockOut) {
                $validator->errors()->add('break_end_1', '休憩時間もしくは退勤時間が不適切な値です');
            }

            if ($end2 && $end2 > $clockOut) {
                $validator->errors()->add('break_end_2', '休憩時間もしくは退勤時間が不適切な値です');
            }
        });
    }
}
