<?php

namespace Tests\Feature\User\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // ログインテストで使用する一般ユーザーの登録
    private function createBaseUser(array $override = [])
    {
        return User::factory()->create(array_merge([
            'name' => 'ログインテストユーザー',
            'email' => 'login-test@example.co.jp',
            'password' => Hash::make('password'),
        ], $override));
    }

    // メールアドレス未入力のバリデーションチェック
    public function test_login_email_not_entered_validation_error()
    {
        $this->createBaseUser();

        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    // パスワードが未入力のバリデーションチェック
    public function test_login_password_not_entered_validation_error()
    {
        $this->createBaseUser();

        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => 'login-test@example.co.jp',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    // 登録内容が一致しない場合のバリデーションチェック
    public function test_login_information_mismatch_error()
    {
        $this->createBaseUser();

        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => 'wrong@example.co.jp',
            'password' => 'password',
        ]);

        $response->assertSessionHas('errorMessage', 'ログイン情報が登録されていません');
    }
}
