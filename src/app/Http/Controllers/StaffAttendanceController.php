<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    // スタッフ一覧画面表示
    public function indexStaffList()
    {
        $users = User::select('id', 'name', 'email')->get();
        return view('admin.staff.list', compact('users'));
    }

    public function indexStaffAttendanceList($id)
    {
        $user = User::findOrFail($id);

        return view('admin.staff.attendance_list', compact('user'));
    }
}
