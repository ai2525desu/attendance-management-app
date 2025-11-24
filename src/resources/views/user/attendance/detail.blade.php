@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/detail.css') }}">
@endsection

@section('content')
<div class="user-attendance-detail__content">
    <h1 class="user-attendance-detail__title">
        勤怠詳細
    </h1>
    <form class="user-attendance-detail__correction-form">
        <table class="correction-form__table">
            <tr class="correction-form__line">
                <th class="correction-form__heading">名前</th>
                <td class="correction-form__item">取得してきたユーザー名</td>
            </tr>
            <tr class="correction-form__line">
                <th class="correction-form__heading">日付</th>
                <td class="correction-form__item">取得してきた日付</td>
            </tr>
            <tr class="correction-form__line">
                <th class="correction-form__heading">出勤・退勤</th>
                <td class="correction-form__item">取得してきた出勤時間～退勤時間</td>
            </tr>
            <tr class="correction-form__line">
                {{--@foreach ()--}}
                <th class="correction-form__heading">休憩+count()</th>
                <td class="correction-form__item">取得してきた休憩時間：foreach必須</td>
                {{--@endforeach--}}
            </tr>
            <!-- 枠の確認のため -->
            <tr class="correction-form__line">
                {{--@foreach ()--}}
                <th class="correction-form__heading">休憩+count()</th>
                <td class="correction-form__item">取得してきた休憩時間：foreach必須</td>
                {{--@endforeach--}}
            </tr>
            <tr class="correction-form__line">
                <th class="correction-form__heading">備考
                </th>
                <td class="correction-form__item">備考入力欄:font-size:14pxなので注意</td>
            </tr>
        </table>
        <div class="correction-form__button">
            <button class="correction-form__button--submit" type="submit">修正</button>
        </div>
    </form>
</div>
@endsection