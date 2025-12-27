@extends('admin.layout.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/approval.css') }}">
@endsection

@section('content')
<div class="admin-amendment-approval-screen__content">
    <h1 class="admin-amendment-approval-screen__title">
        勤怠詳細
    </h1>
    <!-- action未記述 -->
    <form class="admin-amendment-approval-screen__approved-form" method="post" action="" novalidate>
        @csrf
        <input type="hidden" name="user_id" value="{{ $attendanceRequest->user->id }}">
        <input type="hidden" name="attendance_id" value="{{ $attendanceRequest->attendance->id }}">
        <input type="hidden" name="work_date" value="{{ $attendanceRequest->attendance->work_date }}">
        <!-- type=hiddenで管理者IDは入れるのか？ -->
        <table class="approved-form__table">
            <tr class="approved-form__line">
                <th class="approved-form__heading">
                    名前
                </th>
                <td class="approved-form__item">
                    <div class="approved-form__item--information">
                        <span class="approved-form__text right">
                            {{ $attendanceRequest->user->name }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="approved-form__line">
                <th class="approved-form__heading">
                    日付
                </th>
                <td class="approved-form__item">
                    <div class="approved-form__item--information">
                        <span class="approved-form__text right">
                            {{ $attendanceRequest->attendance->work_date->format('Y年') }}
                        </span>
                        <span class="approved-form__text left">
                            {{ $attendanceRequest->attendance->work_date->format('m月d日') }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="approved-form__line">
                <th class="approved-form__heading">
                    出勤・退勤
                </th>
                <td class="approved-form__item">
                    <div class="approved-form__item--information">
                        <input class="approved-form__input right" type="text" name="correct_clock_in" value="{{ $display['correct_clock_in'] }}" readonly>
                        <span class="approved-form__text middle">~</span>
                        <input class="approved-form__input left" type="text" name="correct_clock_out" value="{{ $display['correct_clock_out'] }}" readonly>
                    </div>
                </td>
            </tr>
            @foreach ($display['correct_breaks'] as $key => $break)
            <tr class="approved-form__line">
                <th class="approved-form__heading">
                    休憩{{ $loop->first ? '' : $loop->iteration }}
                </th>
                <td class="approved-form__item">
                    <div class="approved-form__item--information">
                        <input class="approved-form__input right" type="text" name="correct_break_start[{{ $key }}][start]" value="{{ $break['start'] }}" readonly>
                        <span class="approved-form__text middle">~</span>
                        <input class="approved-form__input left" type="text" name="correct_break_end[{{ $key }}][end]" value="{{ $break['end'] }}" readonly>
                    </div>
                </td>
            </tr>
            @endforeach
            <tr class="approved-form__line">
                <th class="approved-form__heading">
                    休憩{{ $new + 1 }}
                </th>
                <td class="approved-form__item">
                    <div class="approved-form__item--information">
                        <input class="approved-form__input right" type="text" name="correct_break_start[{{ $new }}][start]" value="" readonly>
                        <span class="correction-form__text middle">~</span>
                        <input class="approved-form__input left" type="text" name="correct_break_end[{{ $new }}][end]" value="" readonly>
                    </div>
                </td>
            </tr>
            <tr class="approved-form__line">
                <th class="approved-form__heading">
                    備考
                </th>
                <td class="approved-form__item">
                    <div class="approved-form__item--remark">
                        <textarea class="approved-form__textarea" name="remarks" readonly>{{ $attendanceRequest->remarks }}</textarea>
                    </div>
                </td>
            </tr>
        </table>
        <div class="approved-form__button-wrap">
            <div class="approved-form__button">
                <button class="approved-form__button--submit">
                    承認
                </button>
            </div>
        </div>
    </form>
</div>
@endsection