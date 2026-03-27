<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者ユーザーは全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        // 管理者ユーザー（role = 1）
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        // 一般ユーザーを3人作成（role = 0）
        $users = User::factory()->count(3)->create([
            'role' => 0,
        ]);

        // 管理者としてアクセス
        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/staff/list');

        // ステータス確認
        $response->assertStatus(200);

        // 一般ユーザー全員の氏名・メールアドレスが表示されていること
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }
}