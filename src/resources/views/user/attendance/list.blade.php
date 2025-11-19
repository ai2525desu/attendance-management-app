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
        前月当月翌月の移動バー
    </div>
    <div class="user-attendance-list__monthly-attendance">
        選択した月の勤怠一覧
    </div>
</div>
@endsection