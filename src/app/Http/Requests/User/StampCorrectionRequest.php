<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in'      => ['required', 'date_format:H:i'],
            'clock_out'     => ['required', 'date_format:H:i'],
            'break1_in'     => ['nullable', 'date_format:H:i'],
            'break1_out'    => ['nullable', 'date_format:H:i'],
            'break2_in'     => ['nullable', 'date_format:H:i'],
            'break2_out'    => ['nullable', 'date_format:H:i'],
            'note'          => ['required'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn  = $this->clock_in;
            $clockOut = $this->clock_out;

            // 出勤 > 退勤 の場合
            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩1
            if ($this->break1_in) {
                if ($this->break1_in < $clockIn || $this->break1_in > $clockOut) {
                    $validator->errors()->add('break1_in', '休憩時間が不適切な値です');
                }
            }

            if ($this->break1_out) {
                if ($this->break1_out > $clockOut) {
                    $validator->errors()->add('break1_out', '休憩時間もしくは退勤時間が不適切な値です');
                }
            }

            // 休憩2
            if ($this->break2_in) {
                if ($this->break2_in < $clockIn || $this->break2_in > $clockOut) {
                    $validator->errors()->add('break2_in', '休憩時間が不適切な値です');
                }
            }

            if ($this->break2_out) {
                if ($this->break2_out > $clockOut) {
                    $validator->errors()->add('break2_out', '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'clock_in.required'  => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'note.required'      => '備考を記入してください',
        ];
    }
}
