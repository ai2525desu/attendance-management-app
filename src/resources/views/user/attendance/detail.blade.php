@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/detail.css') }}">
@endsection

@section('content')
<div class="user-attendance-detail__content">
    <h1 class="user-attendance-detail__title">
        勤怠詳細
    </h1>
    <!-- input type="time"の件で検討 -->
    <form class="user-attendance-detail__correction-form" method="post" action="">
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <input type="hidden" name="work_date" value="{{ $attendance->work_date->format('Y-m-d') }}">
        <table class="correction-form__table">
            <tr class="correction-form__line">
                <th class="correction-form__heading">名前</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--date">
                        <span class="correction-form__item--text">
                            {{ $user->name }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="correction-form__line">
                <th class="correction-form__heading">日付</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--date">
                        <span class="correction-form__item--text">
                            {{ $attendance->work_date->format('Y年') }}
                        </span>
                        <span class="correction-form__item--text">
                            {{ $attendance->work_date->format('m月d日') }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="correction-form__line">
                <th class="correction-form__heading">出勤・退勤</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--date">
                        <input class="correction-form__item--input" type="datetime" name="correct_clock_in" value="{{ old('correct_clock_in',$attendance?->clock_in->format('H:i')) }}">
                        <span class="correction-form__item--text">~</span>
                        <input class="correction-form__item--input" type="datetime" name="correct_clock_out" value="{{ old('correct_clock_out', $attendance?->clock_out->format('H:i')) }}">
                    </div>
                </td>
            </tr>
            @foreach ($attendance->attendanceBreaks as $index => $break)
            <tr class="correction-form__line">
                <th class="correction-form__heading">休憩{{ $index + 1 }}</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--date">
                        <input class="correction-form__item--input" type="datetime" name="correct_break_start[{{ $index }}][start]" value="{{ old('correct_break_start', $break?->break_start->format('H:i')) }}">
                        <span class="correction-form__item--text">~</span>
                        <input class="correction-form__item--input" type="datetime" name="correct_break_end[{{ $index }}][end]" value="{{ old('correct_break_end', $break?->break_end->format('H:i')) }}">
                    </div>
                </td>
            </tr>
            @endforeach
            <tr class="correction-form__line">
                <th class="correction-form__heading">
                    備考
                </th>
                <td class="correction-form__item">
                    <div class="correction-form__item--date">
                        <textarea class="correction-form__item--textarea" name="remarks">{{ old('remarks') }}</textarea>
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