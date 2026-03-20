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

        // 管理者ユーザー
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 一般ユーザーを2人作成
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 今日の勤怠データ
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

        // ★ 管理者ガードでログインする
        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/attendance/list');

        $response->assertStatus(200);

        // 表示確認
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
        // 今日
        $today = now();

        // 管理者ユーザー
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 管理者ガードでアクセス
        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/attendance/list');

        // ステータス OK
        $response->assertStatus(200);

        // Blade の表示形式に合わせて確認
        $expectedDate = $today->format('Y年n月j日');

        $response->assertSee($expectedDate);
    }

    /** @test */
    public function 前日ボタンで前日の勤怠情報が表示される()
    {
        // 今日と前日
        $today = now();
        $yesterday = $today->copy()->subDay();

        // 管理者ユーザー
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 一般ユーザー
        $user = User::factory()->create();

        // 前日の勤怠データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // ★ 前日ボタン押下後の遷移先（?date=YYYY-MM-DD）
        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/attendance/list?date=' . $yesterday->toDateString());

        // ステータス OK
        $response->assertStatus(200);

        // 日付表示（Blade の形式に合わせる）
        $expectedDate = $yesterday->format('Y年n月j日');
        $response->assertSee($expectedDate);

        // 勤怠データが表示されている
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 翌日ボタンで翌日の勤怠情報が表示される()
    {
        // 今日と翌日
        $today = now();
        $tomorrow = $today->copy()->addDay();

        // 管理者ユーザー
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 一般ユーザー
        $user = User::factory()->create();

        // 翌日の勤怠データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $tomorrow->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // ★ 翌日ボタン押下後の遷移先（?date=YYYY-MM-DD）
        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/attendance/list?date=' . $tomorrow->toDateString());

        // ステータス OK
        $response->assertStatus(200);

        // 日付表示（Blade の形式に合わせる）
        $expectedDate = $tomorrow->format('Y年n月j日');
        $response->assertSee($expectedDate);

        // 勤怠データが表示されている
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}