@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/registration.css') }}">
@endsection

@section('content')
<div class="attendance-registration__message">
    @if (session('successMessage'))
    <div class="attendance-registration__message--success">
        {{ session('successMessage') }}
    </div>
    @endif
    @if (session('errorMessage'))
    <div class="attendance-registration__message--error">
        {{ session('errorMessage') }}
    </div>
    @endif
</div>
<div class="attendance-registration__wrap">
    <div class="attendance-registration__content">
        <div class="attendance-registration__status">
            <div class="attendance-registration__status--working-status">
                <span class="working-status">{{ $status }}</span>
            </div>
            <p class="attendance-registration__status--working-day">{{ $dateTime->isoFormat('Y年M月D日(ddd)') }}</p>
            <p class="attendance-registration__status--current-time" id="current-time">
                {{ $dateTime->format('H:i') }}
            </p>
        </div>
        <div class="attendance-registration__form">
            @if ($status === '勤務外')
            <form class="form__attendance-to-work" action="{{ route('registration.clock_in') }}" method="post">
                @csrf
                <button class="form__attendance-button--submit" type="submit">
                    出勤
                </button>
            </form>
            @elseif ($status === '出勤中')
            <div class="attendance-registration__form--at-work">
                <form class="form__leaving-work" action="{{ route('registration.clock_out') }}" method="post" novalidate>
                    @csrf
                    <button class="form__attendance-button--submit" type="submit">
                        退勤
                    </button>
                </form>
                <form class="form__breake-start" action="{{ route('registration.break_start') }}" method="post">
                    @csrf
                    <button class="form__break-button--submit">
                        休憩入
                    </button>
                </form>
            </div>
            @elseif ($status === '休憩中')
            <form class="form__breake-end" action="{{ route('registration.break_end') }}" method="post">
                @csrf
                <button class="form__break-button--submit">
                    休憩戻
                </button>
            </form>
            @else
            <p class="attendance-registration__clock-out">
                お疲れ様でした。
            </p>
        </div>
        @endif
    </div>
</div>

<script>
    function updateTime() {
        const now = new Date();
        const time = now.toLocaleTimeString('ja-JP', {
            hour: "2-digit",
            minute: "2-digit"
        });
        document.getElementById('current-time').textContent = time;
    }
    updateTime();
    setInterval(updateTime, 1000);
</script>
@endsection