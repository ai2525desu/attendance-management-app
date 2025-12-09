<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceCorrectRequest;

class AttendanceBreakCorrect extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correct_request_id',
        'correct_break_start',
        'correct_break_end',
    ];

    protected $casts = [
        'correct_break_start' => 'datetime',
        'correct_break_end' => 'datetime',
    ];


    public function attendanceCorrectRequest()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class);
    }
}
