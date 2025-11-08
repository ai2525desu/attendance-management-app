@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/auth/login.css') }}">
@endsection

@section('content')
@if(session('errorMessage'))
<div class="login-alert">
    <div class="login-alert__error">
        {{ session('errorMessage') }}
    </div>
</div>
@endif
<div class="user-login-form__content">
    <div class="user-login-form__form-wrap">
        <h1 class="user-login-form__heading">
            ログイン
        </h1>
        <form class="user-login-form__form" action="{{ route('user.auth.login') }}" method="post" novalidate>
            @csrf
            <div class="user-login-form__group">
                <div class="user-login-form__group">
                    <div class="user-login-form__group--title">
                        <label for="email" class="user-login-form__group--label">
                            メールアドレス
                        </label>
                    </div>
                    <div class="user-login-form__group--content">
                        <input id="email" type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="user-login-form__group--error">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="user-login-form__group">
                    <div class="user-login-form__group--title">
                        <label for="password" class="user-login-form__group--label">
                            パスワード
                        </label>
                    </div>
                    <div class="user-login-form__group--content">
                        <input id="password" type="password" name="password">
                    </div>
                    <div class="user-login-form__group--error">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="user-login-form__button">
                    <button class="user-login-form__button--submit" type="submit">
                        ログインする
                    </button>
                </div>
            </div>
        </form>
        <div class="user-login-form__content--screen-transition">
            <a class="screen-transition__register" href="{{ route('user.auth.register') }}">
                会員登録はこちら
            </a>
        </div>
    </div>
</div>
@endsection