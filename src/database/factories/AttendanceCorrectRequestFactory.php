<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrectRequest;
use App\Models\Attendance;
use App\Models\User;

class AttendanceCorrectRequestFactory extends Factory
{
    protected $model = AttendanceCorrectRequest::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'status' => 'pending',
            'reason' => 'テスト理由',
        ];
    }
}
