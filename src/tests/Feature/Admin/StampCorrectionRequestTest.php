<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\AttendanceCorrectRequest;

class StampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者は承認待ちの修正申請一覧を確認できる()
    {
        // 管理者作成 & ログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 承認待ちデータを3件作成
        $pending1 = AttendanceCorrectRequest::factory()->create(['status' => 'pending']);
        $pending2 = AttendanceCorrectRequest::factory()->create(['status' => 'pending']);
        $pending3 = AttendanceCorrectRequest::factory()->create(['status' => 'pending']);

        // 承認済みデータ（表示されないはず）
        $approved = AttendanceCorrectRequest::factory()->create(['status' => 'approved']);

        // 一覧ページへアクセス
        $response = $this->get('/stamp_correction_request/list');

        // ステータス200
        $response->assertStatus(200);

        // 承認待ちの3件が表示されていること
        $response->assertSee($pending1->id);
        $response->assertSee($pending2->id);
        $response->assertSee($pending3->id);

        // 承認済みは表示されないこと
        $response->assertDontSee("/stamp_correction_request/approve/{$approved->id}");
    }

    /** @test */
    public function 承認済みに管理者が承認した修正申請が全て表示されている()
    {
        // 1. 一般ユーザー作成
        $user = User::factory()->create();

        // 2. 管理者ユーザー作成（Userモデルでrole=admin）
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 3. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 4. 一般ユーザーが修正申請を送信
        $this->actingAs($user)->post(
            route('stamp_correction_request.store', ['attendanceId' => $attendance->id]),
            [
                'clock_in' => '08:30',
                'clock_out' => '18:00',
                'note' => '承認テスト',
            ]
        );

        // 5. DB から修正申請を取得し、管理者が承認
        $request = AttendanceCorrectRequest::first();
        $request->update([
            'status' => 'approved',
        ]);

        // 6. 管理者として承認済み一覧（PG12）を開く
        // ★ actingAs を get の直前にチェーンするのが重要
        $response = $this->actingAs($admin, 'admin')
            ->get(route('stamp_correction_request.list', ['tab' => 'approved']));

        // 7. 承認済みタブに表示されていることを確認
        $response->assertSee('承認テスト');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        // 1. 管理者ユーザー作成
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 2. 一般ユーザー作成
        $user = User::factory()->create();

        // 3. 勤怠データ作成（PG13 はこれを表示する）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 4. 修正申請データ作成
        $request = AttendanceCorrectRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'reason' => 'テスト理由',
            'status' => 'pending',
        ]);

        // 5. 管理者として詳細画面（PG13）を開く
        $response = $this->actingAs($admin, 'admin')
            ->get("/stamp_correction_request/approve/{$request->id}");

        // 6. 申請内容が表示されていることを確認
        $response->assertSee('テスト理由');        // 申請理由
        $response->assertSee($user->name);         // 申請者名
        $response->assertSee('2026-03-19');        // 対象日

        // ★ PG13 は元の勤怠情報を表示する
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        // 1. 管理者ユーザー作成
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 2. 一般ユーザー作成
        $user = User::factory()->create();

        // 3. 元の勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
            'note' => '元の備考',
        ]);

        // 4. 修正申請データ作成
        $request = AttendanceCorrectRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'reason' => '修正理由テスト',
            'status' => 'pending',
        ]);

        // 5. 管理者として承認処理を実行
        $this->actingAs($admin, 'admin')
            ->post("/stamp_correction_request/approve/{$request->id}");

        // 6. DBをリフレッシュして再取得
        $request->refresh();
        $attendance->refresh();

        // 7. 修正申請が approved になっていること
        $this->assertEquals('approved', $request->status);

        // 8. 勤怠情報は仕様書通り「備考は更新されない」
        $this->assertEquals('09:00', $attendance->clock_in->format('H:i'));
        $this->assertEquals('18:00', $attendance->clock_out->format('H:i'));
        $this->assertEquals('元の備考', $attendance->note);
    }
}
