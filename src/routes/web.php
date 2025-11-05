<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffAttendanceController;
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

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/attendance', [AttendanceController::class, 'create'])->name('user.attendance.registration');
});

// 管理者関連
Route::get('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.auth.login');
Route::post('/admin/login', [AuthController::class, 'authenticateAdmin']);

Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'logoutAdmin']);

    Route::get('/admin/attendance/list', [AttendanceController::class, 'indexAdmin'])->name('admin.attendance.list');

    Route::get('/admin/staff/list', [StaffAttendanceController::class, 'indexStaffList'])->name('admin.staff.list');

    Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'indexCorrection'])->name('stamp_collection_request.list');
});
