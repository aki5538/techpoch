<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'   => null, // テスト側で指定する
            'work_date' => $this->faker->date('Y-m-d'),
            'clock_in'  => null,
            'clock_out' => null,
            'status'    => '勤務外',
            'note'      => null,
        ];
    }
}
