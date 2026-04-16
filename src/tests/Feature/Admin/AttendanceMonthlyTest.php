<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceMonthlyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 指定ユーザーの月次勤怠情報が正しく表示される()
    {
        $fixedMonth = '2026-03';
        $date = '2026-03-01';

        $admin = User::factory()->create(['role' => 1]);

        $user = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::parse('2026-03-01')->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get("/admin/attendance/staff/{$user->id}?month={$fixedMonth}");

        $response->assertStatus(200);

        $response->assertSee($user->name);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 前月ボタンで前月の情報が表示される()
    {
        $admin = User::factory()->create(['role' => 1]);

        $user = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-03");

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-02");

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 翌月ボタンで翌月の情報が表示される()
    {
        $admin = User::factory()->create(['role' => 1]);

        $user = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-03");

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-04");

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 詳細ボタンを押すとその日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create(['role' => 1]);

        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-03");

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee('2026年');
        $response->assertSee('3月1日');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
