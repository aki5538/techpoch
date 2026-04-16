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
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $pending1 = AttendanceCorrectRequest::factory()->create(['status' => 'pending']);
        $pending2 = AttendanceCorrectRequest::factory()->create(['status' => 'pending']);
        $pending3 = AttendanceCorrectRequest::factory()->create(['status' => 'pending']);

        $approved = AttendanceCorrectRequest::factory()->create(['status' => 'approved']);

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);

        $response->assertSee($pending1->id);
        $response->assertSee($pending2->id);
        $response->assertSee($pending3->id);

        $response->assertDontSee("/stamp_correction_request/approve/{$approved->id}");
    }

    /** @test */
    public function 承認済みに管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $this->actingAs($user)->post(
            route('stamp_correction_request.store', ['attendanceId' => $attendance->id]),
            [
                'clock_in' => '08:30',
                'clock_out' => '18:00',
                'note' => '承認テスト',
            ]
        );

        $request = AttendanceCorrectRequest::first();
        $request->update([
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('stamp_correction_request.list', ['tab' => 'approved']));

        $response->assertSee('承認テスト');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $request = AttendanceCorrectRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'reason' => 'テスト理由',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/stamp_correction_request/approve/{$request->id}");

        $response->assertSee('テスト理由');
        $response->assertSee($user->name);
        $response->assertSee('2026-03-19');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
            'note' => '元の備考',
        ]);

        $request = AttendanceCorrectRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'reason' => '修正理由テスト',
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/stamp_correction_request/approve/{$request->id}");

        $request->refresh();
        $attendance->refresh();

        $this->assertEquals('approved', $request->status);

        $this->assertEquals('09:00', $attendance->clock_in->format('H:i'));
        $this->assertEquals('18:00', $attendance->clock_out->format('H:i'));
        $this->assertEquals('元の備考', $attendance->note);
    }
}
