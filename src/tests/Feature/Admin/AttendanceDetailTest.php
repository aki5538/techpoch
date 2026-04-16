<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 詳細画面に選択した勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => 'テスト備考',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '15:00:00',
            'break_out' => '15:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
                         ->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);

        $response->assertSee('テスト太郎');
        $response->assertSee('2024年');
        $response->assertSee('1月10日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('15:00');
        $response->assertSee('15:30');
        $response->assertSee('テスト備考');
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーになる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => '元の備考',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '15:00:00',
            'break_out' => '15:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->post('/admin/attendance/' . $attendance->id, [
                            'clock_in' => '20:00',
                            'clock_out' => '10:00',

                            'break_start_1' => '12:00',
                            'break_end_1'   => '13:00',
                            'break_start_2' => '15:00',
                            'break_end_2'   => '15:30',

                            'note' => 'テスト',
                        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => '元の備考',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '15:00:00',
            'break_out' => '15:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->post('/admin/attendance/' . $attendance->id, [
                            'clock_in' => '09:00',
                            'clock_out' => '18:00',

                            'break_start_1' => '20:00',
                            'break_end_1'   => '21:00',

                            'break_start_2' => '15:00',
                            'break_end_2'   => '15:30',

                            'note' => 'テスト',
                        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'break_start_1' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーになる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => '元の備考',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '15:00:00',
            'break_out' => '15:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->post('/admin/attendance/' . $attendance->id, [
                            'clock_in' => '09:00',
                            'clock_out' => '18:00',

                            'break_start_1' => '12:00',
                            'break_end_1'   => '20:00', // ← 退勤より後

                            'break_start_2' => '15:00',
                            'break_end_2'   => '15:30',

                            'note' => 'テスト',
                        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'break_end_1' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => '元の備考',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '15:00:00',
            'break_out' => '15:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->post('/admin/attendance/' . $attendance->id, [
                            'clock_in' => '09:00',
                            'clock_out' => '18:00',

                            'break_start_1' => '12:00',
                            'break_end_1'   => '13:00',

                            'break_start_2' => '15:00',
                            'break_end_2'   => '15:30',

                            'note' => '', // ← 未入力
                        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}