<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceApproval;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'request_date',
        'correct_clock_in',
        'correct_clock_out',
        'correct_break_start',
        'correct_break_end',
        'remarks',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function attendanceApproval()
    {
        return $this->hasOne(AttendanceApproval::class);
    }
}
