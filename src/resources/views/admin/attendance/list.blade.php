@extends('admin.layout.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
<div class="admin-attendance-list__content">
    <h1 class="admin-attendance-list__title">
        {{ $targetDate->isoFormat('Y年M月D日') }}の勤怠
    </h1>
    <div class="admin-attendance-list__pagination">
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
    </div>
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
                <tr class="day-attendance__item-line">
                    <td class="day-attendance__item">
                        {{ $attendance->user->name }}
                    </td>
                    <td class="day-attendance__item">
                        {{ $attendance->clock_in->format('H:i') }}
                    </td>
                    <td class="day-attendance__item">
                        {{ $attendance?->clock_out?->format('H:i') }}
                    </td>
                    <td class="day-attendance__item">
                        {{ $attendance->displayBreakTimeInHourFormat() }}
                    </td>
                    <td class="day-attendance__item">
                        {{ $attendance->displayWorkingTimeInHourFormat() }}
                    </td>
                    <td class="day-attendance__item">
                        <a class="day-attendance__screen-transition" href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}">
                            詳細
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection