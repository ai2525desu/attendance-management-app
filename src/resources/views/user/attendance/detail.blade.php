@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/detail.css') }}">
@endsection

@section('content')
<div class="user-attendance-detail__content">
    <h1 class="user-attendance-detail__title">
        勤怠詳細
    </h1>
    <form class="user-attendance-detail__correction-form" method="post" action="">
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <input type="hidden" name="work_date" value="{{ $attendance->work_date->format('Y-m-d') }}">
        <table class="correction-form__table">
            <tr class="correction-form__line">
                <th class="correction-form__heading">名前</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--information">
                        <span class="correction-form__text right">
                            {{ $user->name }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="correction-form__line">
                <th class="correction-form__heading">日付</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--information">
                        <span class="correction-form__text right">
                            {{ $attendance->work_date->format('Y年') }}
                        </span>
                        <span class="correction-form__text left">
                            {{ $attendance->work_date->format('m月d日') }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="correction-form__line">
                <th class="correction-form__heading">出勤・退勤</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--information">
                        <input class="correction-form__input right" type="text" name="correct_clock_in" value="{{ old('correct_clock_in',$attendance?->clock_in->format('H:i')) }}">
                        <span class="correction-form__text middle">~</span>
                        <input class="correction-form__input left" type="text" name="correct_clock_out" value="{{ old('correct_clock_out', $attendance?->clock_out->format('H:i')) }}">
                    </div>
                </td>
            </tr>
            @if ($attendance->attendancebreaks->count() >= 2)
            @foreach ($attendance->attendanceBreaks as $index => $break)
            <tr class="correction-form__line">
                <th class="correction-form__heading">休憩{{ $loop->first ? '' : $loop->iteration }}</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--information">
                        <input class="correction-form__input right" type="text" name="correct_break_start[{{ $index }}][start]" value="{{ old('correct_break_start.$index.start', $break?->break_start->format('H:i')) }}">
                        <span class="correction-form__text middle">~</span>
                        <input class="correction-form__input left" type="text" name="correct_break_end[{{ $index }}][end]" value="{{ old('correct_break_end.$index.end', $break?->break_end->format('H:i')) }}">
                    </div>
                </td>
            </tr>
            @endforeach
            @elseif ($attendance->attendancebreaks->count() === 1)
            <tr class="correction-form__line">
                <th class="correction-form__heading">休憩</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--information">
                        <input class="correction-form__input right" type="text" name="correct_break_start[0][start]" value="{{ old('correct_break_start.0.start', $attendance->attendanceBreaks[0]?->break_start->format('H:i')) }}">
                        <span class="correction-form__text middle">~</span>
                        <input class="correction-form__input left" type="text" name="correct_break_end[0][end]" value="{{ old('correct_break_end.0.end', $attendance->attendanceBreaks[0]?->break_end->format('H:i')) }}">
                    </div>
                </td>
            </tr>
            <tr class="correction-form__line">
                <th class="correction-form__heading">休憩2</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--information">
                        <input class="correction-form__input right" type="text" name="correct_break_start[1][start]" value="{{ old('correct_break_start.1.start') }}">
                        <span class="correction-form__text middle">~</span>
                        <input class="correction-form__input left" type="text" name="correct_break_end[1][end]" value="{{ old('correct_break_end.1.end') }}">
                    </div>
                </td>
            </tr>
            @else
            <tr class="correction-form__line">
                <th class="correction-form__heading">休憩</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--information">
                        <input class="correction-form__input right" type="text" name="correct_break_start[0][start]" value="{{ old('correct_break_start.0.start') }}">
                        <span class="correction-form__text middle">~</span>
                        <input class="correction-form__input left" type="text" name="correct_break_end[0][end]" value="{{ old('correct_break_end.0.end') }}">
                    </div>
                </td>
            </tr>
            <tr class="correction-form__line">
                <th class="correction-form__heading">休憩2</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--information">
                        <input class="correction-form__input" type="text" name="correct_break_start[1][start]" value="{{ old('correct_break_start.1.start') }}">
                        <span class="correction-form__text">~</span>
                        <input class="correction-form__input" type="text" name="correct_break_end[1][end]" value="{{ old('correct_break_end.1.end') }}">
                    </div>
                </td>
            </tr>
            @endif
            <tr class="correction-form__line">
                <th class="correction-form__heading">
                    備考
                </th>
                <td class="correction-form__item">
                    <div class="correction-form__item--remark">
                        <textarea class="correction-form__textarea" name="remarks">{{ old('remarks') }}</textarea>
                    </div>
                </td>
            </tr>
        </table>
        <div class="correction-form__button">
            <button class="correction-form__button--submit" type="submit">修正</button>
        </div>
    </form>
</div>
@endsection