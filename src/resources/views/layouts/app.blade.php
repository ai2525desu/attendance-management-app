<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
    @yield('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <div class="layout">
        <header class="header">
            <div class="header__inner">
                <div class="header__logo">
                    @if (Auth::guard('admin')->check())
                    <a class="header__logo--item" href="{{ route('admin.attendance.list') }}">
                        <img src="{{ asset('storage/CoachTech_Logo.svg') }}" alt="CoachTech_Logo">
                    </a>
                    @elseif (Auth::guard('web')->check())
                    <a class="header__logo" href="{{ route('user.attendance.registration') }}">
                        <img src="{{ asset('storage/CoachTech_Logo.svg') }}" alt="CoachTech_Logo">
                    </a>
                    @else
                    @if (request()->is('admin/*'))
                    <a class="header__logo" href="{{ route('admin.auth.login') }}">
                        <img src="{{ asset('storage/CoachTech_Logo.svg') }}" alt="CoachTech_Logo">
                    </a>
                    @else
                    <a class="header__logo" href="{{ route('user.auth.login') }}">
                        <img src="{{ asset('storage/CoachTech_Logo.svg') }}" alt="CoachTech_Logo">
                    </a>
                    @endif
                    @endif
                </div>
                <div class="header__item">
                    @if (Auth::guard('admin')->check())
                    <div class="header__item--nav">
                        <nav class="nav__wrap">
                            <ul class="nav__list">
                                <li class="nav__item">
                                    <!-- {{--href="{{ route('admin.attendance.list') }}--}} -->
                                    <a class="screen-transition" href="">勤怠一覧</a>
                                </li>
                                <li class="nav__item">
                                    <!-- {{--href="{{ route('admin.staff.list')--}} }}" -->
                                    <a class="screen-transition" href="">スタッフ一覧</a>
                                </li>
                                <li class="nav__item">
                                    <!-- {{--href="{{ route('stamp_collection_request.list')--}} }}" -->
                                    <a class="screen-transition" href="">申請一覧</a>
                                </li>
                                <li class="nav__item">
                                    <form class="logout-button" method="post" action="/admin/logout">
                                        @csrf
                                        <button class="logout-button__submit" type="submit">ログアウト</button>
                                    </form>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    @elseif (Auth::guard('web')->check())
                    <div class="header__item--nav">
                        <nav class="nav__wrap">
                            <ul class="nav__list">
                                <li class="nav__item">
                                    <!-- {{--href="{{ route('user.attendance.registration') }}--}} -->
                                    <a class="screen-transition" href="">勤怠</a>
                                </li>
                                <li class="nav__item">
                                    <!-- {{--href="{{ route('user.attendance.list')--}} }}" -->
                                    <a class="screen-transition" href="">勤怠一覧</a>
                                </li>
                                <li class="nav__item">
                                    <!-- {{--href="{{ route('stamp_collection_request.list')--}} }}" -->
                                    <a class="screen-transition" href="">申請</a>
                                </li>
                                <li class="nav__item">
                                    <form class="logout-button" method="post" action="/logout">
                                        @csrf
                                        <button class="logout-button__submit" type="submit">ログアウト</button>
                                    </form>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    @else
                    <!-- 未ログイン状態の時は何も表示しない -->
                    @endif
                </div>
            </div>
        </header>
    </div>
    <div class="main">
        @yield('content')
    </div>
</body>

</html>