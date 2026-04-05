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
}
