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
        // ★ Blade の表示と合わせて月を固定
        $fixedMonth = '2026-03';
        $date = '2026-03-01';

        // 管理者
        $admin = User::factory()->create(['role' => 1]);

        // 一般ユーザー
        $user = User::factory()->create(['role' => 0]);

        // 勤怠データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::parse('2026-03-01')->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // ★ ?month=2026-03 を付けてアクセス
        $response = $this->actingAs($admin, 'admin')
                        ->get("/admin/attendance/staff/{$user->id}?month={$fixedMonth}");

        $response->assertStatus(200);

        // 氏名
        $response->assertSee($user->name);

        // 勤怠データ
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 前月ボタンで前月の情報が表示される()
    {
        // 1. 管理者ユーザーにログイン
        $admin = User::factory()->create(['role' => 1]);

        // 2. 一般ユーザー（勤怠対象）
        $user = User::factory()->create(['role' => 0]);

        // 3. 前月（2026-02）の勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 4. 今月（2026-03）でアクセス
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-03");

        // 5. 「前月」ボタンを押す（リンク先へアクセス）
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-02");

        // 6. 前月の勤怠が表示されていること
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 翌月ボタンで翌月の情報が表示される()
    {
        // 1. 管理者ユーザーにログイン
        $admin = User::factory()->create(['role' => 1]);

        // 2. 一般ユーザー（勤怠対象）
        $user = User::factory()->create(['role' => 0]);

        // 3. 翌月（2026-04）の勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 4. 今月（2026-03）でアクセス
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-03");

        // 5. 「翌月」ボタンを押す（リンク先へアクセス）
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-04");

        // 6. 翌月の勤怠が表示されていること
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 詳細ボタンを押すとその日の勤怠詳細画面に遷移する()
    {
        // 1. 管理者ユーザーにログイン
        $admin = User::factory()->create(['role' => 1]);

        // 2. 一般ユーザー（勤怠対象）
        $user = User::factory()->create(['role' => 0]);

        // 3. 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 4. 勤怠一覧ページにアクセス（PG08）
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-03");

        // 5. 「詳細」ボタンのリンク先へアクセス（PG09）
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/{$attendance->id}");

        // 6. 遷移先で勤怠詳細が表示されていること
        $response->assertStatus(200);

        // ★ Blade の表示形式に合わせて修正
        $response->assertSee('2026年');   // 年
        $response->assertSee('3月1日');   // 月日

        $response->assertSee('09:00');    // 出勤
        $response->assertSee('18:00');    // 退勤
    }
}
