<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceCorrectionController extends Controller
{
    // 申請一覧画面表示
    public function indexCorrection()
    {
        return view('stamp_collection_request.list');
    }
}
