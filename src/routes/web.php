<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffAttendanceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 一般ユーザー関連
Route::get('/login', [AuthController::class, 'login'])->name('user.auth.login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/register', [AuthController::class, 'register'])->name('user.auth.register');
Route::post('/register', [AuthController::class, 'store']);

Route::get('/email/verify', function () {
    return view('user.auth.verify_email');
})->middleware('auth')->name('verification.notice');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->SendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('user.attendance.registration');
})->middleware(['auth:web', 'signed'])->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/attendance', [AttendanceController::class, 'create'])->name('user.attendance.registration');
    Route::post('/attendance/clock_in', [AttendanceController::class, 'clockIn'])->name('registration.clock_in');
    Route::post('/attendance/clock_out', [AttendanceController::class, 'clockOut'])->name('registration.clock_out');
    Route::post('/attendance/break_start', [AttendanceController::class, 'breakStart'])->name('registration.break_start');
    Route::post('/attendance/break_end', [AttendanceController::class, 'breakEnd'])->name('registration.break_end');

    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('user.attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'editDetail'])->name('user.attendance.detail');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'storeCorrection'])->name('user.attendance.storeCorrection');

    Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'indexCorrection'])->name('user.stamp_correction_request.list');
});

// 管理者関連
Route::get('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.auth.login');
Route::post('/admin/login', [AuthController::class, 'authenticateAdmin']);

Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'logoutAdmin']);

    Route::get('/admin/attendance/list', [AttendanceController::class, 'indexAdmin'])->name('admin.attendance.list');
    Route::get('/admin/attendance/{id}', [AttendanceController::class, 'editAdminDetail'])->name('admin.attendance.detail');

    Route::get('/admin/staff/list', [StaffAttendanceController::class, 'indexStaffList'])->name('admin.staff.list');

    Route::get('/admin/stamp_correction_request/list', [AttendanceCorrectionController::class, 'indexAdminCorrection'])->name('admin.stamp_correction_request.list');
});
