<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    // スタッフ一覧画面表示
    public function indexStaffList()
    {
        return view('admin.staff.list');
    }
}
