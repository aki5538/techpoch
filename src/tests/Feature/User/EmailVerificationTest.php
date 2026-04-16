<?php

namespace Tests\Feature\User;

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
        Notification::fake();

        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'taro@example.com')->first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /** @test */
    public function 認証はこちらからボタンを押すとメール認証サイトに遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user, 'user');

        $response = $this->get('/email/verify');

        $response->assertStatus(200);

        $response->assertSee('http://localhost:8025');
    }

    /** @test */
    public function メール認証を完了すると勤怠登録画面に遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user, 'user')->get($verificationUrl);

        $response->assertRedirect('/attendance');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}