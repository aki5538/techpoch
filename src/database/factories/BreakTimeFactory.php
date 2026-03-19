<?php

namespace Database\Factories;

use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        return [
            'attendance_id' => null,
            'break_in' => '2026-03-19 12:00:00',
            'break_out' => '2026-03-19 12:30:00',
        ];
    }
}