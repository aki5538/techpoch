<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 本番と同じガードを使うようにテスト環境を揃える
        Auth::shouldUse('user');
    }

    /** @test */
    public function 勤務外の場合_勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 10:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'user');

        $response = $this->get('/attendance');

        $response->assertSee('勤務外');
    }

    /** @test */
public function 出勤中の場合_勤怠ステータスが正しく表示される()
{
    Carbon::setTestNow(Carbon::parse('2023-06-01 10:00:00'));

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Attendance::create([
        'user_id'   => $user->id,
        'work_date' => '2023-06-01',
        'clock_in'  => '2023-06-01 09:00:00',
        'status'    => '出勤中',
    ]);

    // actingAs ではなく be を使う
    $this->be($user, 'user');

    $response = $this->get('/attendance');

    $response->assertSee('出勤中');
}
}