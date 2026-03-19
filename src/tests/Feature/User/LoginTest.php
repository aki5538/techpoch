<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合はエラーになる()
    {
        // まずユーザーを作成（ログインには必須）
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // メールアドレスを空にしてログインを試みる
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        // email にエラーが付くことを確認
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function パスワードが未入力の場合はエラーになる()
    {
        // 1. ユーザーを登録する
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2. パスワード以外のユーザー情報を入力する
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '', // ← 未入力
        ]);

        // 3. バリデーションエラーを確認
        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function 登録内容と一致しない場合はエラーになる()
    {
        // 1. ユーザーを登録する
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2. 誤ったメールアドレスでログインを試みる
        $response = $this->post('/login', [
            'email' => 'wrong@example.com', // ← わざと間違える
            'password' => 'password',
        ]);

        // 3. エラーメッセージが出ることを確認
        $response->assertSessionHasErrors(['email']);
    }
}