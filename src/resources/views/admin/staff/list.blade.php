@extends('admin.layout.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">
@endsection

@section('content')
<div class="staff-list__content">
    <h1 class="staff-list__title">
        スタッフ一覧
    </h1>
    <div class="staff-list__table-wrap">
        <table class="staff-list__table">
            <thead>
                <tr class="staff-list__table-header">
                    <th class="staff-list__heading">名前</th>
                    <th class="staff-list__heading">メールアドレス</th>
                    <th class="staff-list__heading">月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr class="staff-list__item-line">
                    <td class="staff-list__item">
                        {{ $user->name }}
                    </td>
                    <td class="staff-list__item">
                        {{ $user->email }}
                    </td>
                    <td class="staff-list__item">
                        <a class="staff-list__screen-transition" href="{{ route('admin.staff.attendance_list', ['id' => $user->id]) }}">
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