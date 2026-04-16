<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 当日の全ユーザーの勤怠情報が一覧に表示される()
    {
        $today = now()->toDateString();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => $today,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => $today,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee($user1->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee($user2->name);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 遷移時に現在の日付が表示される()
    {
        $today = now();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/attendance/list');

        $response->assertStatus(200);

        $expectedDate = $today->format('Y年n月j日');

        $response->assertSee($expectedDate);
    }

    /** @test */
    public function 前日ボタンで前日の勤怠情報が表示される()
    {
        $today = now();
        $yesterday = $today->copy()->subDay();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/attendance/list?date=' . $yesterday->toDateString());

        $response->assertStatus(200);

        $expectedDate = $yesterday->format('Y年n月j日');
        $response->assertSee($expectedDate);

        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 翌日ボタンで翌日の勤怠情報が表示される()
    {
        $today = now();
        $tomorrow = $today->copy()->addDay();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $tomorrow->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/attendance/list?date=' . $tomorrow->toDateString());

        $response->assertStatus(200);

        $expectedDate = $tomorrow->format('Y年n月j日');
        $response->assertSee($expectedDate);

        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}