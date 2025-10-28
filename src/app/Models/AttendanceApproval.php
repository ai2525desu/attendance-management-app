<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceCollectRequest;

class AttendanceApproval extends Model
{
    use HasFactory;

    public function attendanceCorrectRequest()
    {
        return $this->belongsTo(AttendanceCollectRequest::class);
    }
}
