<?php

namespace Tests\Feature\User\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    // 基本のデータを規定
    private function validRegistrationData(array $override = [])
    {
        $baseData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.co.jp',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];
        return array_merge($baseData, $override);
    }

    // 名前を未入力時のバリデーションチェック
    public function test_registration_name_not_entered_validation_error()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', $this->validRegistrationData(['name' => '']));
        $response->assertSessionHasErrors(['name']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    // メールアドレス未入力のバリデーションチェック
    public function test_registration_email_not_entered_validation_error()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', $this->validRegistrationData(['email' => '']));
        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    // パスワードが8文字未満のバリデーションチェック
    public function test_registration_password_length_validation_error()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', $this->validRegistrationData(['password' => 'pass']));
        $response->assertSessionHasErrors(['password']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    // パスワードの不一致のバリデーションチェック
    public function test_registration_password_mismatch_validation_error()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', $this->validRegistrationData(['password' => 'password', 'password_confirmation' => 'password123']));
        $response->assertSessionHasErrors(['password_confirmation']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('パスワードと一致しません', $errors->first('password_confirmation'));
    }

    // パスワード未入力のバリデーションチェック
    public function test_registration_password_not_entered_validation_error()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', $this->validRegistrationData(['password' => '']));
        $response->assertSessionHasErrors(['password']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }
}
