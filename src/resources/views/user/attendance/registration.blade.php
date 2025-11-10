@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/registration.css') }}">
@endsection

@section('content')
<div class="attendance-registration__content">
    <div class="attendance-registration__item">
        <div class="attendance-registration__item--working-status">
            <span class="working-status">勤務外</span>
        </div>
        <div class="attendance-registration__item--working-day">
            <!-- コントローラーでCarbonを使用して取得かな？ -->
            <p class="working-day">xxxx年x月x日</p>
        </div>
        <div class="attendance-registration__item--current-time">
            <p class="current-time">00:00（現在時刻表示）</p>
        </div>
    </div>
    <div class="attendance-registration__form">
        <form class="" action="" method="post" novalidate>
            @csrf
            <div class="attendance-registration__form-button">
                <button class="attendance-registration__form-button--submit" type="submit">
                    出勤
                </button>
            </div>
        </form>
    </div>
</div>
@endsection