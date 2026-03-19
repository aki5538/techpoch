<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合はエラーになる()
    {
        // 1. 管理者ユーザーを登録する（role=1）
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1, // ← あなたのDBの管理者判定カラムに合わせて変更
        ]);

        // 2. メールアドレスを空にしてログインを試みる
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // 3. email にバリデーションエラーが付くことを確認
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function パスワードが未入力の場合はエラーになる()
    {
        // 1. 管理者ユーザーを登録する（role=1）
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1, // ← あなたのDBの管理者判定カラムに合わせて変更
        ]);

        // 2. パスワードを空にしてログインを試みる
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        // 3. password にバリデーションエラーが付くことを確認
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function 登録内容と一致しない場合はエラーになる()
    {
        // 1. 正しい管理者ユーザーを登録する（role=1）
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1, // ← あなたのDBの管理者判定カラムに合わせて変更
        ]);

        // 2. 誤ったメールアドレスでログインを試みる
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        // 3. login に「ログイン情報が登録されていません」が付くことを確認
        $response->assertSessionHasErrors([
            'login' => 'ログイン情報が登録されていません',
        ]);
    }
}
