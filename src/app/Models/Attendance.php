<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceBreaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function attendanceCorrectRequest()
    {
        return $this->hasOne(AttendanceCorrectRequest::class);
    }

    // 休憩時間の合計を分単位で行う
    public function totalBreakTimeInMinutes()
    {
        return $this->attendanceBreaks->sum(function ($break) {
            if (!$break->break_start || !$break->break_end) {
                return 0;
            }
            $breakStart = Carbon::parse($break->break_start);
            $breakEnd = Carbon::parse($break->break_end);
            return $breakEnd->diffInMinutes($breakStart);
        });
    }

    // 休憩時間をHH:MMの形式で表示
    public function displayBreakTimeInHourFormat()
    {
        // $minutes = $this->totalBreakTimeInMinutes();
        // $hours = floor($minutes / 60);
        // $mins = $minutes % 60;
        // return sprintf('%02d:%02d', $hours, $mins);
        return gmdate('H:i', $this->totalBreakTimeInMinutes() * 60);
    }

    // 出勤から退勤までの勤務時間を分単位で合計
    public function totalWorkMinutes()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }
        $clockIn = Carbon::parse($this->clock_in);
        $clockOut = Carbon::parse($this->clock_out);
        return $clockOut->diffInMinutes($clockIn);
    }

    // 実働時間（分単位）= 勤務時間-休憩時間
    public function totalActualWorkingTimeInMinutes()
    {
        return max(0, $this->totalWorkMinutes() - $this->totalBreakTimeInMinutes());
    }

    // 実働時間をHH::MMの形式で表示
    public function displayWorkingTimeInHourFormat()
    {
        // $minutes = $this->totalActualWorkingTimeInMinutes();
        // $hours = floor($minutes / 60);
        // $mins = $minutes % 60;
        // return sprintf('%02d:%02d', $hours, $mins);
        return gmdate('H:i', $this->totalActualWorkingTimeInMinutes() * 60);
    }
}
