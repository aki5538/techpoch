<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録後に認証メールが送信される()
    {
        // メール送信をフェイク
        Notification::fake();

        // 1. 会員登録をする
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 登録されたユーザーを取得
        $user = User::where('email', 'taro@example.com')->first();

        // 2. 認証メールが送信されたことを確認
        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /** @test */
    public function 認証はこちらからボタンを押すとメール認証サイトに遷移する()
    {
        // 1. 認証が必要なユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // ログイン状態にする
        $this->actingAs($user, 'user');

        // 2. メール認証誘導画面を表示
        $response = $this->get('/email/verify');

        $response->assertStatus(200);

        // 3. ボタンのリンク先が MailHog であることを確認
        $response->assertSee('http://localhost:8025');
    }

    /** @test */
    public function メール認証を完了すると勤怠登録画面に遷移する()
    {
        // 認証されていないユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証リンク（署名付きURL）を生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        // 認証リンクにアクセス（ログイン状態で）
        $response = $this->actingAs($user, 'user')->get($verificationUrl);

        // 認証後に勤怠登録画面へリダイレクトされることを確認
        $response->assertRedirect('/attendance');

        // email_verified_at が更新されていることを確認
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}