<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
use App\Models\AttendanceCollectRequest;

class AttendanceApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'attendance_collect_request_id',
        'approved_date',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function attendanceCorrectRequest()
    {
        return $this->belongsTo(AttendanceCollectRequest::class);
    }
}
