@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
<div class="login-form__content">
    <div class="login-form__form-wrap">
        <h1 class="login-form__heading">
            管理者ログイン
        </h1>
        <form class="login-form__form" action="{{ route('admin.auth.login') }}" method="post" novalidate>
            @csrf
            <div class="login-form__group">
                <div class="login-form__group">
                    <div class="login-form__group--title">
                        <label for="email" class="login-form__group--label">
                            メールアドレス
                        </label>
                    </div>
                    <div class="login-form__group--content">
                        <input id="email" type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="login-form__group--error">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="login-form__group">
                    <div class="login-form__group--title">
                        <label for="password" class="login-form__group--label">
                            パスワード
                        </label>
                    </div>
                    <div class="login-form__group--content">
                        <input id="password" type="password" name="password">
                    </div>
                    <div class="login-form__group--error">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="login-form__button">
                    <button class="login-form__button--submit" type="submit">
                        管理者ログインする
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection