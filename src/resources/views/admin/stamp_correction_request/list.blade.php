@extends('admin.layout.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/list.css') }}">
@endsection

@section('content')
@if (session('message'))
<div class="admin-corection-list__session-message">
    {{ session('message') }}
</div>
@endif
<div class="admin-corection-list__content">
    <h1 class="admin-corection-list__title">
        申請一覧
    </h1>
    <div class="admin-corection-list__tab">
        <div class="correction-tab__header">
            <ul class="correction-tab__header-inner">
                <li class="correction-tab__item">
                    <a class="item__pending-list {{ $tab === 'pending' ? 'is-active' : '' }}" href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}">承認待ち</a>
                </li>
                <li class="correction-tab__item">
                    <a class="item__approved-list {{ $tab === 'approved' ? 'is-active' : '' }}" href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}">承認済み</a>
                </li>
            </ul>
        </div>
        <div class="correction-tab__body">
            <table class="correction-tab__table {{ $tab === 'pending' ? 'is-active' : '' }}">
                <thead>
                    <tr class="correction-tab__table-header">
                        <th class="correction-tab__table-heading">
                            状態
                        </th>
                        <th class="correction-tab__table-heading">
                            名前
                        </th>
                        <th class="correction-tab__table-heading">
                            対象日時
                        </th>
                        <th class="correction-tab__table-heading">
                            申請理由
                        </th>
                        <th class="correction-tab__table-heading">
                            申請日時
                        </th>
                        <th class="correction-tab__table-heading">
                            詳細
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($corrections as $correction)
                    <tr class="correction-tab__table-information-line">
                        <td class="correction-tab__table-information">
                            {{ $correction->status_text }}
                        </td>
                        <td class="correction-tab__table-information">
                            {{ $correction->user->name }}
                        </td>
                        <td class="correction-tab__table-information">
                            {{ $correction->attendance->work_date->isoFormat('YYYY/MM/DD') }}
                        </td>
                        <td class="correction-tab__table-information">
                            <div class="table-information__remarks">
                                {{ $correction->remarks }}
                            </div>
                        </td>
                        <td class="correction-tab__table-information">
                            {{ $correction->request_date->isoFormat('YYYY/MM/DD') }}
                        </td>
                        <td class="correction-tab__table-information">
                            <a class="correction-tab__screen-transition" href="{{ route('admin.stamp_correction_request.approval', ['attendance_correct_request_id' => $correction->id]) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <table class="correction-tab__table {{ $tab === 'approved' ? 'is-active' : '' }}">
                <thead>
                    <tr class="correction-tab__table-header">
                        <th class="correction-tab__table-heading">
                            状態
                        </th>
                        <th class="correction-tab__table-heading">
                            名前
                        </th>
                        <th class="correction-tab__table-heading">
                            対象日時
                        </th>
                        <th class="correction-tab__table-heading">
                            申請理由
                        </th>
                        <th class="correction-tab__table-heading">
                            申請日時
                        </th>
                        <th class="correction-tab__table-heading">
                            詳細
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($corrections as $correction)
                    <tr class="correction-tab__table-information-line">
                        <td class="correction-tab__table-information">
                            {{ $correction->status_text }}
                        </td>
                        <td class="correction-tab__table-information">
                            {{ $correction->user->name }}
                        </td>
                        <td class="correction-tab__table-information">
                            {{ $correction->attendance->work_date->isoFormat('YYYY/MM/DD') }}
                        </td>
                        <td class="correction-tab__table-information">
                            <div class="table-information__remarks">
                                {{ $correction->remarks }}
                            </div>
                        </td>
                        <td class="correction-tab__table-information">
                            {{ $correction->request_date->isoFormat('YYYY/MM/DD') }}
                        </td>
                        <td class="correction-tab__table-information">
                            <a class="correction-tab__screen-transition" href="{{ route('admin.stamp_correction_request.approval', ['attendance_correct_request_id' => $correction->id]) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection