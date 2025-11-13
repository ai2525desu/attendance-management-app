@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/registration.css') }}">
@endsection

@section('content')
<div class="attendance-registration__message">
    セッションメッセージ
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
<div class="attendance-registration__content">
    <div class="attendance-registration__item">
        <div class="attendance-registration__item--working-status">
            <span class="working-status">{{ $status }}</span>
        </div>
        <div class="attendance-registration__item--working-day">
            <p class="working-day">{{ $dateTime->isoFormat('Y年M月D日(ddd)') }}</p>
        </div>
        <div class="attendance-registration__item--current-time">
            <p class="current-time" id="current-time">{{ $dateTime->format('H:i') }}</p>
        </div>
    </div>
    <div class="attendance-registration__item">
        @if ($status === '勤務外')
        <div class="attendance-registration__item--form">
            <form class="form__attendance-to-work" action="{{ route('registration.clock_in') }}" method="post">
                @csrf
                <div class="form__attendance-button">
                    <button class="form__attendance-button--submit" type="submit">
                        出勤
                    </button>
                </div>
            </form>
        </div>
        @elseif ($status === '出勤中')
        <div class="attendance-registration__item--form">
            <form class="form__leaving-work" action="{{ route('registration.clock_out') }}" method="post" novalidate>
                @csrf
                <div class="form__attendance-button">
                    <button class="form__attendance-button--submit" type="submit">
                        退勤
                    </button>
                </div>
            </form>
            <form class="form__breake-start" action="{{ route('registration.break_start') }}" method="post">
                @csrf
                <div class="form__break-button">
                    <button class="form__break-button--submit">
                        休憩入
                    </button>
                </div>
            </form>
        </div>
        @elseif ($status === '休憩中')
        <div class="attendance-registration__item--form">
            <form class="form__breake-end" action="{{ route('registration.break_end') }}" method="post">
                @csrf
                <div class="form__break-button">
                    <button class="form__break-button--submit">
                        休憩戻
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="attendance-registration__item--form">
            <div class="attendance-registration__clock-out">
                お疲れ様でした。
            </div>
        </div>
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