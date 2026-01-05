<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAdminTest extends TestCase
{
    use RefreshDatabase;

    // 管理者ログインテストで使用する管理者の登録
    private function createBaseAdmin(array $override = [])
    {
        // 管理者は固定で1名のため、AdminFactoryのfixedに明示
        return Admin::factory()->fixed()->create($override);
    }

    // メールアドレス未入力のバリデーションチェック
    public function test_login_admin_email_not_entered_validation_error()
    {
        $this->createBaseAdmin();

        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'adminpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    // パスワードが未入力のバリデーションチェック
    public function test_login_admin_password_not_entered_validation_error()
    {
        $this->createBaseAdmin();

        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    // 登録内容が一致しない場合のバリデーションチェック
    public function test_login_admin_information_mismatch_error()
    {
        $this->createBaseAdmin();

        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.co.jp',
            'password' => 'adminpassword',
        ]);

        $response->assertSessionHas('errorMessage', 'ログイン情報が登録されていません');
    }
}
