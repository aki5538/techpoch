<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceCorrectRequest;


class AttendanceFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id'   => User::factory(), // テスト側で指定する
            'work_date' => $this->faker->date('Y-m-d'),
            'clock_in'  => null,
            'clock_out' => null,
            'status'    => '勤務外',
            'note'      => null,
        ];
    }
}
