@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/auth/verify_email.css') }}">
@endsection

@section('content')
<div class="verify-email-content__send-message">
    @if (session('message'))
    <div class="verify-email-content__send-message--success">
        {{ session('message') }}
    </div>
    @endif
    @if (session('errorMessage'))
    <div class="verify-email-content__send-message--uncertified">
        {{ session('errorMessage') }}
    </div>
    @endif
</div>
<div class="verify-email-content__wrap">
    <div class="verify-email-content__notice">
        <p class="verify-email-content__notice--text">
            登録していただいたメールアドレスに認証メールを送付しました。<br />
            メール認証を完了してください。
        </p>
    </div>
    <div class="verify-email-content__form--first-send-button">
        <a class="first-send-button__submit" href="http://localhost:8025/">
            認証はこちらから
        </a>
    </div>
    <form class="verify-email-content__form" method="post" action="{{ route('verification.send') }}">
        @csrf
        <div class="verify-email-content__form--resend-email-button">
            <button class="resend-email-button__submit" type="submit">
                認証メールを再送する
        </div>
    </form>
</div>
@endsection