@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/list.css') }}">
@endsection

@section('content')
<div class="user-attendance-list__content">
    <h2 class="user-attendance-list__title">
        勤怠一覧
    </h2>
    <div class="user-attendance-list__pagination">
        <a class="pagination__month" href="{{ route('user.attendance.list', ['year' => $previous->year, 'month' => $previous->month]) }}">
            <img class="pagination__month--arrow" src="{{ asset('storage/arrow.svg') }}" alt="←">
            <span class="pagination__month--title">
                前月
            </span>
        </a>
        <a class="pagination__month" href="{{ route('user.attendance.list') }}">
            <img class="pagination__month--calendar-icon" src="{{ asset('storage/calendar.svg') }}" alt="カレンダーアイコン">
            <span class="pagination__month--title current-month">
                {{ $targetDate->format('Y/m') }}
            </span>
        </a>
        <a class="pagination__month" href="{{ route('user.attendance.list', ['year' => $next->year, 'month' => $next->month]) }}">
            <span class="pagination__month--title">
                翌月
            </span>
            <img class="pagination__month--arrow right-direction" src="{{ asset('storage/arrow.svg') }}" alt="→">
        </a>
    </div>
    <div class="user-attendance-list__monthly-attendance">
        <table class="monthly-attendance__table">
            <thead>
                <tr class="monthly-attendance__table-header">
                    <th class="monthly-attendance__heading">
                        <span class="date-inner">日付</span>
                    </th>
                    <th class="monthly-attendance__heading">出勤</th>
                    <th class="monthly-attendance__heading">退勤</th>
                    <th class="monthly-attendance__heading">休憩</th>
                    <th class="monthly-attendance__heading">合計</th>
                    <th class="monthly-attendance__heading">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daysInMonth as $day)
                <tr class="monthly-attendance__item-line">
                    <td class="monthly-attendance__item">
                        <span class="date-inner">
                            {{ $day['date']->isoFormat('MM/DD(ddd)') }}
                        </span>
                    </td>
                    <td class="monthly-attendance__item">
                        {{ $day['attendance'] ? $day['attendance']->clock_in->format('H:i') : '' }}
                    </td>
                    <td class="monthly-attendance__item">
                        {{ $day['attendance'] ? $day['attendance']->clock_out->format('H:i') : '' }}
                    </td>
                    <td class="monthly-attendance__item">
                        {{ $day['total_break_format']}}
                    </td>
                    <td class="monthly-attendance__item">
                        {{ $day['total_working_time_format']}}
                    </td>
                    <td class="monthly-attendance__item">
                        @if ($day['attendance'])
                        <a class="monthly-attendance__screen-transition" href="{{ route('user.attendance.detail', ['id' => $day['attendance']->id]) }}">
                            詳細
                        </a>
                        @else
                        <span class="monthly-attendance__screen-transition">詳細</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection