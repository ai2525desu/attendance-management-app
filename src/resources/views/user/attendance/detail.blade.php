@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/detail.css') }}">
@endsection

@section('content')
@if (session('message'))
<div class="user-attendance-detail__session-message">
    {{ session('message') }}
    セッションメッセージ
</div>
@endif
<div class="user-attendance-detail__content">
    <h1 class="user-attendance-detail__title">
        勤怠詳細
    </h1>
    <form class="user-attendance-detail__correction-form" method="post" action="{{ route('user.attendance.storeCorrection', ['id' => $attendance->id]) }}" novalidate>
        @csrf
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
                    <div class="correction-form__item--information {{ $applyingFixes ? 'pending' : '' }}">
                        <input class="correction-form__input right" type="text" name="correct_clock_in" value="{{ old('correct_clock_in', $display['correct_clock_in']) }}" {{ $applyingFixes ? 'readonly' : '' }}>
                        <span class="correction-form__text middle">~</span>
                        <input class="correction-form__input left" type="text" name="correct_clock_out" value="{{ old('correct_clock_out', $display['correct_clock_out']) }}" {{ $applyingFixes ? 'readonly' : '' }}>
                    </div>
                    <div class="correction-form__item--error">
                        @if ($clockInError)
                        <span class="error-message">
                            {{ $clockInError }}
                        </span>
                        @endif
                        @if ($clockOutError)
                        <span class="error-message">
                            {{ $clockOutError }}
                        </span>
                        @endif
                    </div>
                </td>
            </tr>
            @foreach ($display['breaks'] as $index => $break)
            <tr class="correction-form__line">
                <!-- <th class="correction-form__heading">休憩{{ $loop->first ? '' : $loop->iteration }}</th> -->
                <th class="correction-form__heading">休憩{{ $index + 1}}</th>
                <td class="correction-form__item">
                    <div class="correction-form__item--information {{ $applyingFixes ? 'pending' : '' }}">
                        <input class="correction-form__input right" type="text" name="correct_break_start[{{ $index }}][start]" value="{{ old("correct_break_start.$index.start", $break['start']) }}" {{ $applyingFixes ? 'readonly' : '' }}>
                        <span class="correction-form__text middle">~</span>
                        <input class="correction-form__input left" type="text" name="correct_break_end[{{ $index }}][end]" value="{{ old("correct_break_end.$index.end", $break['end']) }}" {{ $applyingFixes ? 'readonly' : '' }}>
                    </div>
                    <div class="correction-form__item--error">
                        @error("correct_break_start.$index.start")
                        <span class="error-message">
                            {{ $message }}
                        </span>
                        @enderror
                        @error("correct_break_end.$index.end")
                        <span class="error-message">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                </td>
            </tr>
            @endforeach
            {{--<!-- <th class="correction-form__heading">休憩{{ $display['breaks'] ? count($display['breaks']) + 1 : 1 }}</th>
            <th class="correction-form__heading">休憩{{ $breakIndex + 1 }}</th>
            <td class="correction-form__item">
                <div class="correction-form__item--information {{ $applyingFixes ? 'pending' : '' }}">
                    <input class="correction-form__input right" type="text" name="correct_break_start[{{ $breakIndex }}][start]" value="{{ old("correct_break_start.$breakIndex.start", $display['breaks'][$breakIndex]['start']) }}" {{ $applyingFixes ? 'readonly' : '' }}>
                    <span class="correction-form__text middle">~</span>
                    <input class="correction-form__input left" type="text" name="correct_break_end[{{ $breakIndex }}][end]" value="{{ old("correct_break_end.$breakIndex.end", $display['breaks'][$breakIndex]['end']) }}" {{ $applyingFixes ? 'readonly' : '' }}>
                </div>
                <div class="correction-form__item--error">
                    @error("correct_break_start.$breakIndex.start")
                    <span class="error-message">
                        {{ $message }}
                    </span>
                    @enderror
                    @error("correct_break_end.$breakIndex.end")
                    <span class="error-message">
                        {{ $message }}
                    </span>
                    @enderror
                </div>
            </td>
            </tr> -->--}}
            <tr class="correction-form__line">
                <th class="correction-form__heading">
                    備考
                </th>
                <td class="correction-form__item">
                    <div class="correction-form__item--remark {{ $applyingFixes ? 'pending' : '' }}">
                        <textarea class="correction-form__textarea {{ $applyingFixes ? 'pending' : '' }}" name="remarks" {{ $applyingFixes ? 'readonly' : '' }}>{{ old('remarks', $amendmentApplication?->remarks) }}</textarea>
                    </div>
                    <div class="correction-form__item--error">
                        <span class="error-message">
                            @error('remarks')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                </td>
            </tr>
        </table>
        <div class="correction-form__button-wrap">
            @if (!$applyingFixes)
            <div class="correction-form__button">
                <button class="correction-form__button--submit" type="submit">
                    修正
                </button>
            </div>
            @else
            <div class="correction-form__button ">
                <span class="correction-form__button--none">
                    *承認待ちのため修正はできません。
                </span>
            </div>
            @endif
        </div>
    </form>
</div>
@endsection