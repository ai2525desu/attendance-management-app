@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/auth/register.css') }}">
@endsection

@section('content')
<div class="user-register-form__content">
    <div class="user-register-form__form-wrap">
        <h1 class="user-register-form__heading">
            会員登録
        </h1>
        <form class="user-register-form__form" action="{{ route('user.auth.register') }}" method="post" novalidate>
            @csrf
            <div class="user-register-form__group">
                <div class="user-register-form__group--title">
                    <label for="name" class="user-register-form__group--label">
                        名前
                    </label>
                </div>
                <div class="user-register-form__group--content">
                    <input id="name" type="text" name="name" value="{{ old('name') }}">
                </div>
                <div class="user-register-form__group--error">
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="user-register-form__group">
                <div class="user-register-form__group--title">
                    <label for="email" class="user-register-form__group--label">
                        メールアドレス
                    </label>
                </div>
                <div class="user-register-form__group--content">
                    <input id="email" type="email" name="email" value="{{ old('email') }}">
                </div>
                <div class="user-register-form__group--error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="user-register-form__group">
                <div class="user-register-form__group--title">
                    <label for="password" class="user-register-form__group--label">
                        パスワード
                    </label>
                </div>
                <div class="user-register-form__group--content">
                    <input id="password" type="password" name="password">
                </div>
                <div class="user-register-form__group--error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="user-register-form__group">
                <div class="user-register-form__group--title">
                    <label for="password_confirmation" class="user-register-form__group--label">
                        パスワード確認
                    </label>
                </div>
                <div class="user-register-form__group--content">
                    <input id="password_confirmation" type="password" name="password_confirmation">
                </div>
                <div class="user-register-form__group--error">
                    @error('password_confirmation')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="user-register-form__button">
                <button class="user-register-form__button--submit" type="submit">
                    登録する
                </button>
            </div>
        </form>
    </div>
    <div class="user-register-form__content--screen-transition">
        <a class="screen-transition__login" href="{{ route('user.auth.login') }}">
            ログインはこちら
        </a>
    </div>
</div>
@endsection