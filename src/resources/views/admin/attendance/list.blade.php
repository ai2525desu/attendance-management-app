@extends('admin.layout.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
<div class="admin-attendance-list__content">
    <h1 class="user-attendance-list__title">
        {{ $targetDate->isoFormat('Y年M月D日') }}の勤怠
    </h1>
    <div class="user-attendance-list__pagination">
        <a class="pagination__day" href="{{ route('admin.attendance.list', ['date' => $previous]) }}">
            <img class="pagination__day--arrow" src="{{ asset('storage/arrow.svg') }}" alt="←">
            <span class="pagination__day--title">
                前日
            </span>
        </a>
        <a class="pagination__day" href="{{ route('admin.attendance.list') }}">
            <img class="pagination__day--calendar-icon" src="{{ asset('storage/calendar.svg') }}" alt="カレンダーアイコン">
            <span class="pagination__day--title current-day">
                {{ $targetDate->format('Y/m/d') }}
            </span>
        </a>
        <a class="pagination__day" href="{{ route('admin.attendance.list', ['date' => $next]) }}">
            <span class="pagination__day--title">
                翌日
            </span>
            <img class="pagination__day--arrow right-direction" src="{{ asset('storage/arrow.svg') }}" alt="→">
        </a>
        <div class="admin-attendance-list__day-attendance">
            <table class="day-attendance__table">
                <thead>
                    <tr class="day-attendance__table-header">
                        <th class="day-attendance__heading">名前</th>
                        <th class="day-attendance__heading">出勤</th>
                        <th class="day-attendance__heading">退勤</th>
                        <th class="day-attendance__heading">休憩</th>
                        <th class="day-attendance__heading">合計</th>
                        <th class="day-attendance__heading">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                    <tr class="monthly-attendance__item-line">
                        <td class="monthly-attendance__item">
                            {{ $attendance->user->name }}
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