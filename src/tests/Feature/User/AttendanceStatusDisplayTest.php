<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;

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

    /** @test */
    public function 休憩中の場合_勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 10:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01', // ← 今日
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '休憩中',     // ← ここが今回のポイント
        ]);

        $this->be($user, 'user'); // ← userガードでログイン

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

        // 勤務外 → 今日の勤怠レコードなし
        $this->be($user, 'user');

        // ① 出勤ボタンが表示されていること
        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        // ② 出勤処理を実行
        $response = $this->post('/attendance/clock-in');

        // ③ 出勤後の画面でステータスが「出勤中」になっていること
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

        // 今日の勤怠レコード（退勤済）を作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'clock_out' => '2023-06-01 18:00:00',
            'status'    => '退勤済',
        ]);

        $this->be($user, 'user');

        $response = $this->get('/attendance');

        // 出勤ボタンが表示されないことを確認
        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 09:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 勤務外 → 今日の勤怠レコードなし
        $this->be($user, 'user');

        // ① 出勤処理を実行
        $this->post('/attendance/clock-in');

        // ② 勤怠一覧画面へアクセス
        $response = $this->get('/attendance/list');

        // ③ 出勤時刻（09:00）が表示されていることを確認
        $response->assertSee('09:00');
    }

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 10:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 出勤中の勤怠レコードを作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '出勤中',
        ]);

        $this->be($user, 'user');

        // ① 休憩入ボタンが表示されている
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // ② 休憩入処理を実行
        $this->post('/attendance/break-in');

        // ③ ステータスが休憩中になっている
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

        // 出勤中の勤怠レコードを作成
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '出勤中',
        ]);

        $this->be($user, 'user');

        // ① 休憩入 → 休憩中へ
        $this->post('/attendance/break-in');

        // ② 休憩戻 → 出勤中へ
        $this->post('/attendance/break-out');

        // ③ 再び「休憩入」ボタンが表示されることを確認
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

        // 出勤中の勤怠レコードを作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '出勤中',
        ]);

        $this->be($user, 'user');

        // ① 休憩入 → 休憩中へ
        $this->post('/attendance/break-in');

        // ② 休憩戻 → 出勤中へ
        $this->post('/attendance/break-out');

        // ③ ステータスが出勤中に戻っていることを確認
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

        // 出勤中の勤怠レコードを作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '出勤中',
        ]);

        $this->be($user, 'user');

        // ① 休憩入 → 休憩中へ
        $this->post('/attendance/break-in');

        // ② 休憩戻 → 出勤中へ
        $this->post('/attendance/break-out');

        // ③ 再度休憩入 → 休憩中へ
        $this->post('/attendance/break-in');

        // ④ 「休憩戻」ボタンが表示されることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        // 出勤時刻 09:00
        Carbon::setTestNow(Carbon::parse('2023-06-01 09:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 出勤中の勤怠レコードを作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '出勤中',
        ]);

        $this->be($user, 'user');

        // ① 休憩入（10:00）
        Carbon::setTestNow(Carbon::parse('2023-06-01 10:00:00'));
        $this->post('/attendance/break-in');

        // ② 休憩戻（10:30）
        Carbon::setTestNow(Carbon::parse('2023-06-01 10:30:00'));
        $this->post('/attendance/break-out');

        // ③ 勤怠一覧画面で休憩時刻が表示されていることを確認
        $response = $this->get('/attendance/list');

        $response->assertSee('10:00'); // break_in
        $response->assertSee('10:30'); // break_out
    }

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::parse('2023-06-01 18:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 出勤中の勤怠レコードを作成
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2023-06-01',
            'clock_in'  => '2023-06-01 09:00:00',
            'status'    => '出勤中',
        ]);

        $this->be($user, 'user');

        // ① 退勤ボタンが表示されている
        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        // ② 退勤処理
        $this->post('/attendance/clock-out');

        // ③ ステータスが退勤済になっている
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

        // 出勤（09:00）
        Carbon::setTestNow(Carbon::parse('2023-06-01 09:00:00'));
        $this->post('/attendance/clock-in');

        // 退勤（18:00）
        Carbon::setTestNow(Carbon::parse('2023-06-01 18:00:00'));
        $this->post('/attendance/clock-out');

        // 勤怠一覧画面で退勤時刻が表示されていることを確認
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
        // 現在時刻を固定（Figma と同じ 2023/06 に合わせる）
        Carbon::setTestNow(Carbon::parse('2023-06-01'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        $response = $this->get('/attendance/list');

        // 現在の月（2023/06）が表示されていること
        $response->assertSee('2023/06');
    }

    /** @test */
    public function 前月ボタンを押すと前月の情報が表示される()
    {
        // 現在を 2023/06 に固定（Figma と仕様書に合わせる）
        Carbon::setTestNow(Carbon::parse('2023-06-15'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        // 2023/05 の勤怠データ（前月）
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-05-10',
            'clock_in' => '2023-05-10 09:00:00',
        ]);

        // まず 2023/06 の一覧を開く
        $thisMonth = $this->get('/attendance/list');
        $thisMonth->assertSee('2023/06');

        // 「前月」ボタンを押す → 実際は GET パラメータで遷移
        $prevMonth = $this->get('/attendance/list?month=2023-05');

        // 前月（2023/05）が表示されていること
        $prevMonth->assertSee('2023/05');

        // 前月の勤怠データ（05/10）が表示されていること
        $prevMonth->assertSee('05/10');
    }

    /** @test */
    public function 翌月ボタンを押すと翌月の情報が表示される()
    {
        // 現在を 2023/06 に固定（Figma と仕様書に合わせる）
        Carbon::setTestNow(Carbon::parse('2023-06-15'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->be($user, 'user');

        // 翌月（2023/07）の勤怠データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-07-05',
            'clock_in' => '2023-07-05 09:00:00',
        ]);

        // まず 2023/06 の一覧を開く
        $thisMonth = $this->get('/attendance/list');
        $thisMonth->assertSee('2023/06');

        // 「翌月」ボタンを押す → 実際は GET パラメータで遷移
        $nextMonth = $this->get('/attendance/list?month=2023-07');

        // 翌月（2023/07）が表示されていること
        $nextMonth->assertSee('2023/07');

        // 翌月の勤怠データ（07/05）が表示されていること
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

        // 勤怠データを1件作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01',
            'clock_in' => '2023-06-01 09:00:00',
        ]);

        // 一覧画面を開く
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 「詳細」リンクにアクセス
        $detail = $this->get('/attendance/detail/' . $attendance->id);

        // 詳細画面に遷移できていること
        $detail->assertStatus(200);

        // 詳細画面に当日の情報が表示されていること
        $detail->assertSee('2023-06-01');
        $detail->assertSee('09:00');
    }

    /** @test */
    public function 勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        // 1. ユーザー作成
        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
        ]);

        // 3. ログインして詳細ページへアクセス
        $response = $this->actingAs($user)
            ->get(route('user.attendance.detail', ['id' => $attendance->id]));

        // 4. 名前が表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    /** @test */
    public function 勤怠詳細画面の日付が選択した日付になっている()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成（選択した日付を明示）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
        ]);

        // 3. ログインして詳細ページへアクセス
        $response = $this->actingAs($user)
            ->get(route('user.attendance.detail', ['id' => $attendance->id]));

        // 4. 日付が表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('2026-03-19');
    }

    /** @test */
    public function 出勤退勤にて記されている時間がログインユーザーの打刻と一致している()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成（出勤・退勤時刻を明示）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 3. ログインして詳細ページへアクセス
        $response = $this->actingAs($user)
            ->get(route('user.attendance.detail', ['id' => $attendance->id]));

        // 4. 出勤・退勤時刻が表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 休憩にて記されている時間がログインユーザーの打刻と一致している()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
        ]);

        // 3. 休憩データ（休憩1）作成
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '2026-03-19 12:00:00',
            'break_out' => '2026-03-19 12:30:00',
        ]);

        // 4. ログインして詳細ページへアクセス
        $response = $this->actingAs($user)
            ->get(route('user.attendance.detail', ['id' => $attendance->id]));

        // 5. 休憩時間が表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 3. 出勤時間を退勤時間より後にして修正申請
        $response = $this->actingAs($user)
            ->post(route('stamp_correction_request.store', ['id' => $attendance->id]), [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'note' => 'テスト',
            ]);

        // 4. エラーメッセージ確認
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 3. 休憩開始時間を退勤時間より後にして修正申請
        $response = $this->actingAs($user)
            ->post(route('stamp_correction_request.store', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break1_in' => '19:00', // 退勤より後
                'note' => 'テスト',
            ]);

        // 4. エラーメッセージ確認
        $response->assertSessionHasErrors([
            'break1_in' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 3. 休憩終了時間を退勤時間より後にして修正申請
        $response = $this->actingAs($user)
            ->post(route('stamp_correction_request.store', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break1_in' => '10:00',
                'break1_out' => '19:00', // 退勤より後
                'note' => 'テスト',
            ]);

        // 4. エラーメッセージ確認
        $response->assertSessionHasErrors([
            'break1_out' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示される()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 3. 備考未入力で修正申請
        $response = $this->actingAs($user)
            ->post(route('stamp_correction_request.store', ['id' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '', // 未入力
            ]);

        // 4. エラーメッセージ確認
        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function 修正申請処理が実行される()
    {
        // 1. 一般ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 3. 修正申請を送信
        $response = $this->actingAs($user)
            ->post(route('stamp_correction_request.store', ['id' => $attendance->id]), [
                'clock_in' => '08:30',
                'clock_out' => '18:00',
                'note' => 'テスト修正',
            ]);

        // 4. DB に修正申請が作成されていることを確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'reason' => 'テスト修正',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function 承認待ちにログインユーザーが行った申請が全て表示されている()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 3. 修正申請を2件作成（複数表示の確認）
        $this->actingAs($user)->post(route('stamp_correction_request.store', ['id' => $attendance->id]), [
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'note' => '1件目',
        ]);

        $this->actingAs($user)->post(route('stamp_correction_request.store', ['id' => $attendance->id]), [
            'clock_in' => '08:45',
            'clock_out' => '18:00',
            'note' => '2件目',
        ]);

        // 4. 申請一覧画面へアクセス
        $response = $this->actingAs($user)->get(route('stamp_correction_request.list'));

        // 5. DB に2件 pending が存在することを確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'reason' => '1件目',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'reason' => '2件目',
            'status' => 'pending',
        ]);

        // 6. 画面に2件の内容が表示されていることを確認
        $response->assertSee('1件目');
        $response->assertSee('2件目');
    }

    /** @test */
    public function 承認済みに管理者が承認した修正申請が全て表示されている()
    {
        // 1. 一般ユーザー作成
        $user = User::factory()->create();

        // 2. 管理者ユーザー作成
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
        $this->actingAs($user)->post(route('stamp_correction_request.store', ['id' => $attendance->id]), [
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'note' => '承認テスト',
        ]);

        // 5. DB から修正申請を取得
        $request = StampCorrectionRequest::first();

        // 6. 管理者が承認（status を approved に更新）
        $request->update([
            'status' => 'approved',
        ]);

        // 7. 一般ユーザーが申請一覧画面を開く
        $response = $this->actingAs($user)->get(route('stamp_correction_request.list'));

        // 8. 承認済みに表示されていることを確認
        $response->assertSee('承認テスト');
    }

    /** @test */
    public function 各申請の詳細ボタンを押下すると勤怠詳細画面に遷移する()
    {
        // 1. 一般ユーザー作成
        $user = User::factory()->create();

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-19',
            'clock_in' => '2026-03-19 09:00:00',
            'clock_out' => '2026-03-19 18:00:00',
        ]);

        // 3. 修正申請を作成
        $this->actingAs($user)->post(route('stamp_correction_request.store', ['id' => $attendance->id]), [
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'note' => '詳細テスト',
        ]);

        // 4. 申請一覧画面を開く
        $response = $this->actingAs($user)->get(route('stamp_correction_request.list'));

        // 5. 詳細ボタン（リンク）が存在することを確認
        $detailUrl = route('user.attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);

        // 6. 実際に詳細画面へ遷移できることを確認
        $detailResponse = $this->actingAs($user)->get($detailUrl);
        $detailResponse->assertStatus(200);
    }
}