<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;

class AttendanceStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->be($user, 'user');

        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合_勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 10:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '休憩中',
        ]);

        $this->be($user, 'user');

        $response = $this->get('/attendance');

        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合_勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 10:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'clock_out' => '2023-06-01 18:00:00',
            'status'    => '退勤済',
        ]);

        $this->be($user, 'user');

        $response = $this->get('/attendance');

        $response->assertSee('退勤済');
    }

    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 09:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        $response = $this->post('/attendance/clock-in');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回のみ_退勤済の場合は出勤ボタンが表示されない()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 18:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'clock_out' => '2023-06-01 18:00:00',
            'status'    => '退勤済',
        ]);

        $this->be($user, 'user');

        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 09:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        $this->post('/attendance/clock-in');

        $response = $this->get('/attendance/list');

        $response->assertSee('09:00');
    }

    /** @test */
    public function 休憩ボタンが正しく機能する()
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

        $this->be($user, 'user');

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/attendance/break-in');

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 10:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '出勤中',
        ]);

        $this->be($user, 'user');

        $this->post('/attendance/break-in');

        $this->post('/attendance/break-out');

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
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

        $this->be($user, 'user');

        $this->post('/attendance/break-in');

        $this->post('/attendance/break-out');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
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

        $this->be($user, 'user');

        $this->post('/attendance/break-in');

        $this->post('/attendance/break-out');

        $this->post('/attendance/break-in');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 09:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '出勤中',
        ]);

        $this->be($user, 'user');

        Carbon::setTestNow(Carbon::parse('2023-06-01 10:00:00'));
        $this->post('/attendance/break-in');

        Carbon::setTestNow(Carbon::parse('2023-06-01 10:30:00'));
        $this->post('/attendance/break-out');

        $response = $this->get('/attendance/list');

        $response->assertSee('0:30');
    }

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 18:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '出勤中',
        ]);

        $this->be($user, 'user');

        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        Carbon::setTestNow(Carbon::parse('2023-06-01 09:00:00'));
        $this->post('/attendance/clock-in');

        Carbon::setTestNow(Carbon::parse('2023-06-01 18:00:00'));
        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance/list');

        $response->assertSee('18:00');
    }

    /** @test */
    public function 自分の勤怠情報が全て表示されている()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '2023-06-01 09:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-06-02',
            'clock_in' => '2023-06-02 09:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => '2023-06-03',
            'clock_in' => '2023-06-03 09:00:00',
        ]);

        $this->be($user, 'user');

        $response = $this->get('/attendance/list');

        $response->assertSee('06/01');
        $response->assertSee('06/02');
        $response->assertDontSee('06/03');
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        $response = $this->get('/attendance/list');

        $response->assertSee('2023/06');
    }

    /** @test */
    public function 前月ボタンを押すと前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-15'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-05-10',
            'clock_in' => '2023-05-10 09:00:00',
        ]);

        $thisMonth = $this->get('/attendance/list');
        $thisMonth->assertSee('2023/06');

        $prevMonth = $this->get('/attendance/list?month=2023-05');

        $prevMonth->assertSee('2023/05');

        $prevMonth->assertSee('05/10');
    }

    /** @test */
    public function 翌月ボタンを押すと翌月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-15'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-07-05',
            'clock_in' => '2023-07-05 09:00:00',
        ]);

        $thisMonth = $this->get('/attendance/list');
        $thisMonth->assertSee('2023/06');

        $nextMonth = $this->get('/attendance/list?month=2023-07');

        $nextMonth->assertSee('2023/07');

        $nextMonth->assertSee('07/05');
    }

    /** @test */
    public function 詳細ボタンを押すとその日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '2023-06-01 09:00:00',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $detail = $this->get('/attendance/detail/' . $attendance->id);

        $detail->assertStatus(200);

        $detail->assertSee('2023-06-01');
        $detail->assertSee('09:00');
    }

    /** @test */
    public function 勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
        ]);

        $response = $this->actingAs($user)
            ->get(route('user.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    /** @test */
    public function 勤怠詳細画面の日付が選択した日付になっている()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
        ]);

        $response = $this->actingAs($user)
            ->get(route('user.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('2026-03-19');
    }

    /** @test */
    public function 出勤退勤にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get(route('user.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 休憩にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '2026-03-19 12:00:00',
            'break_out' => '2026-03-19 12:30:00',
        ]);

        $response = $this->actingAs($user)
            ->get(route('user.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->post(route('stamp_correction_request.store', ['attendanceId' => $attendance->id]), [
                'clock_in' => '2026-03-19 19:00:00',
                'clock_out' => '2026-03-19 18:00:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->post(route('stamp_correction_request.store', ['attendanceId' => $attendance->id]), [
                'clock_in' => '2026-03-19 08:30:00',
                'clock_out' => '2026-03-19 18:00:00',
                'break1_in' => '2026-03-19 19:00:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'break1_in' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->post(route('stamp_correction_request.store', ['attendanceId' => $attendance->id]), [
                'clock_in' => '2026-03-19 08:30:00',
                'clock_out' => '2026-03-19 18:00:00',
                'break1_in' => '2026-03-19 10:00:00',
                'break1_out' => '2026-03-19 19:00:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'break1_out' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->post(route('stamp_correction_request.store', ['attendanceId' => $attendance->id]), [
                'clock_in' => '2026-03-19 08:30:00',
                'clock_out' => '2026-03-19 18:00:00',
                'note' => '',
            ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function 修正申請処理が実行される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $response = $this->actingAs($user, 'user')
            ->post(route('stamp_correction_request.store', ['attendanceId' => $attendance->id]), [
                'clock_in' => '08:30',
                'clock_out' => '18:00',
                'note' => 'テスト修正',
                'break1_in' => null,
                'break1_out' => null,
                'break2_in' => null,
                'break2_out' => null,
            ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'reason' => 'テスト修正',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function 承認待ちにログインユーザーが行った申請が全て表示されている()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $this->actingAs($user)->post(route('stamp_correction_request.store', ['attendanceId' => $attendance->id]), [
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'note' => '1件目',
        ]);

        $this->actingAs($user)->post(route('stamp_correction_request.store', ['attendanceId' => $attendance->id]), [
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'note' => '2件目',
        ]);

        $response = $this->actingAs($user)->get(route('stamp_correction_request.list'));

        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $user->id,
            'reason' => '1件目',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $user->id,
            'reason' => '2件目',
            'status' => 'pending',
        ]);

        $response->assertSee('1件目');
        $response->assertSee('2件目');
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

        $this->actingAs($user)->post(route('stamp_correction_request.store', ['attendanceId' => $attendance->id]), [
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'note' => '承認テスト',
        ]);

        $request = AttendanceCorrectRequest::first();

        $request->update([
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get(route('stamp_correction_request.list'));

        $response->assertSee('承認テスト');
    }

    /** @test */
    public function 各申請の詳細ボタンを押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        $this->actingAs($user)->post(route('stamp_correction_request.store', ['attendanceId' => $attendance->id]), [
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'note' => '詳細テスト',
        ]);

        $response = $this->actingAs($user)->get(route('stamp_correction_request.list'));

        $detailUrl = route('user.attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);

        $detailResponse = $this->actingAs($user)->get($detailUrl);
        $detailResponse->assertStatus(200);
    }
}