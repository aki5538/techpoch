<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceClockDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠打刻画面に現在の日時が表示される()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 10:30:00'));

        $user = \App\Models\User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'user');

        $response = $this->get('/attendance');

        $response->assertSee('2023年6月1日(木)');

        $response->assertSee('10:30');
    }
}