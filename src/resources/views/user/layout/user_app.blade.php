<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user/layout/user_app.css') }}">
    @yield('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
</head>

<body class="{{ request()->routeIs('user.auth.*') ? 'auth-background' : 'content-background' }}">
    <div class="layout">
        <header class="header">
            <div class="header__inner">
                <div class="header__logo">
                    @if (Auth::guard('web')->check())
                    <a class="header__logo--item" href="{{ route('user.attendance.registration') }}">
                        <img src="{{ asset('storage/CoachTech_Logo.svg') }}" alt="CoachTech_Logo">
                    </a>
                    @else
                    <a class="header__logo--item" href="{{ route('user.auth.login') }}">
                        <img src="{{ asset('storage/CoachTech_Logo.svg') }}" alt="CoachTech_Logo">
                    </a>
                    @endif
                </div>
                <div class="header__item">
                    @if (Auth::guard('web')->check())
                    <div class="header__item--nav">
                        <nav class="nav__wrap">
                            <ul class="nav__list">
                                <li class="nav__item">
                                    <a class="screen-transition" href="{{ route('user.attendance.registration') }}">勤怠</a>
                                </li>
                                <li class=" nav__item">
                                    <a class="screen-transition" href="{{ route('user.attendance.list') }}">勤怠一覧</a>
                                </li>
                                <li class="nav__item">
                                    <a class="screen-transition" href="{{ route('user.stamp_correction_request.list') }}">申請</a>
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
    <main class="main">

        @yield('content')
    </main>
</body>

</html>