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
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $users = User::factory()->count(3)->create([
            'role' => 0,
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get('/admin/staff/list');

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }
}