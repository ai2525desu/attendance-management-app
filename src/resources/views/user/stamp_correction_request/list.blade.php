@extends('user.layout.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/stamp_correction_request/list.css') }}">
@endsection

@section('content')
@if (session('message'))
<div class="user-corection-list__session-message">
    {{ session('message') }}
    セッションメッセージ
</div>
@endif
<div class="user-corection-list__content">
    <h1 class="user-corection-list__title">
        申請一覧
    </h1>
    <div class="user-corection-list__tab">
        <div class="correction-tab__header">
            <li class="correction-tab__item">
                <a class="item__pending-list {{ $tab === 'pending' ? 'is-active' : '' }}" href="{{ route('user.stamp_correction_request.list', ['tab' => 'pending']) }}">承認待ち</a>
            </li>
            <li class="correction-tab__item">
                <a class="item__approved-list {{ $tab === 'approved' ? 'is-active' : '' }}" href="{{ route('user.stamp_correction_request.list', ['tab' => 'approved']) }}">承認済み</a>
            </li>
        </div>
        <div class="correction-tab__body">
            <table class="correction-tab__table {{ $tab === 'pending' ? 'is-active' : '' }}">
                <thead>
                    <tr class="correction-tab__header">
                        <th class="correction-tab__heading">
                            状態
                        </th>
                        <th class="correction-tab__heading">
                            名前
                        </th>
                        <th class="correction-tab__heading">
                            対象日時
                        </th>
                        <th class="correction-tab__heading">
                            申請理由
                        </th>
                        <th class="correction-tab__heading">
                            申請日時
                        </th>
                        <th class="correction-tab__heading">
                            詳細
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{--@foreach ()--}}
                    <tr class="correction-tab__information-line">
                        <td class="correction-tab__information">
                            <!-- status -->
                            承認待ち
                        </td>
                        <td class="correction-tab__information">
                            <!-- user->name -->
                            ユーザー名
                        </td>
                        <td class="correction-tab__information">
                            <!-- work_date -->
                            2000/00/00
                        </td>
                        <td class="correction-tab__information">
                            <!-- remarks -->
                            申請の理由
                        </td>
                        <td class="correction-tab__information">
                            <!-- request_date -->
                            申請した日時
                        </td>
                        <td class="correction-tab__information">
                            <!-- 詳細画面への遷移 -->
                            <a class="correction-tab__screen-transition" href="{{ route('user.attendance.detail', ['id' => $day['attendance']->id]) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                    {{--@endforeach--}}
                </tbody>
            </table>
            <table class="correction-tab__table {{ $tab === 'approved' ? 'is-active' : '' }}">
                <thead>
                    <tr class="correction-tab__header">
                        <th class="correction-tab__heading">
                            状態
                        </th>
                        <th class="correction-tab__heading">
                            名前
                        </th>
                        <th class="correction-tab__heading">
                            対象日時
                        </th>
                        <th class="correction-tab__heading">
                            申請理由
                        </th>
                        <th class="correction-tab__heading">
                            申請日時
                        </th>
                        <th class="correction-tab__heading">
                            詳細
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{--@foreach ()--}}
                    <tr class="correction-tab__information-line">
                        <td class="correction-tab__information">
                            <!-- status -->
                            承認待ち
                        </td>
                        <td class="correction-tab__information">
                            <!-- user->name -->
                            ユーザー名
                        </td>
                        <td class="correction-tab__information">
                            <!-- work_date -->
                            2000/00/00
                        </td>
                        <td class="correction-tab__information">
                            <!-- remarks -->
                            申請の理由
                        </td>
                        <td class="correction-tab__information">
                            <!-- request_date -->
                            申請した日時
                        </td>
                        <td class="correction-tab__information">
                            <!-- 詳細画面への遷移 -->
                            <a class="correction-tab__screen-transition" href="{{ route('user.attendance.detail', ['id' => $day['attendance']->id]) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                    {{--@endforeach--}}
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection